<?php

namespace Modules\Coinv2\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Coin\Events\UserCoinBalanceChanged;
use Modules\Coin\Events\UserCoinLogBadDebt;
use Modules\Coin\Exceptions\CoinLogBadDebtException;
use Modules\Coin\Models\Coin;
use Modules\Coin\Models\CoinAsset;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Translate\TranslateExpression;

class BalanceChangeService
{
    /**
     * @var Coin
     */
    protected $coin;
    /**
     * @var int
     */
    protected $from = 0;
    /**
     * @var int
     */
    protected $to = 0;
    /**
     * @var string
     */
    protected $symbol;
    /**
     * @var int
     */
    protected $num;
    /**
     * @var string
     */
    protected $module;
    /**
     * @var string
     */
    protected $action;
    /**
     * @var int
     */
    protected $no = 0;
    /**
     * @var TranslateExpression
     */
    protected $info;

    /**
     * 转出余额用户 from和to至少设置一个
     * 如果过没有to则表示直接从from中扣除金额
     *
     * @param $from
     *
     * @return $this
     */
    public function from($from)
    {
        $this->from = with_user_id($from);

        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    /**
     * 转入余额用户 from和to至少设置一个
     * 如果没有from则表示直接给to增加余额
     *
     * @param $to
     *
     * @return $this
     */
    public function to($to)
    {
        $this->to = with_user_id($to);

        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    /**
     * 改变的余额币种
     *
     * @param $symbol
     *
     * @return $this
     */
    public function withSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * 改变的余额数量
     *
     * @param $num
     *
     * @return $this
     */
    public function withNum($num)
    {
        if ($num <= 0) {
            throw new InvalidArgumentException(trans("coin::exception.转账金额必须为正整数"));
        }

        $this->num = abs($num);

        return $this;
    }

    public function getNum()
    {
        return $this->num;
    }

    /**
     * @param $module
     *
     * @return $this
     */
    public function withModule($module)
    {
        $pos = strpos($module, '.');

        if ($pos != false) {
            $this->module = substr($module, 0, $pos);
            $this->action = substr($module, $pos + 1, mb_strlen($module));
        } else {
            $this->module = $module;
        }

        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param int $no
     *
     * @return $this
     */
    public function withNo(int $no)
    {
        $this->no = $no;

        return $this;
    }

    public function getNo()
    {
        return $this->no;
    }

    /**
     * @param $info
     *
     * @return $this
     */
    public function withInfo(TranslateExpression $expression)
    {
        $this->info = $expression;

        return $this;
    }

    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param array $options
     *
     * @return array
     * @throws ModelSaveException
     * @throws \Throwable
     */
    public function change(array $options = [])
    {
        if ($options['transaction'] ?? false) {
            // 默认不开启事务, 但是需注意事务导致的数据一致性
            return DB::transaction(function () {
                return $this->changing();
            });
        }

        return $this->changing();
    }

    /**
     * @return array
     * @throws ModelSaveException
     */
    protected function changing()
    {
        $this->checkFromAndTo();

        $this->checkSymbol();

        $this->checkNum();

        $return = [
            'from' => null,
            'to' => null,
            'from_log' => null,
            'to_log' => null,
        ];

        if ($this->from) { // 减少

            //$this->ensureUserAuth($this->from);

            $return['from'] = $balanceModel = $this->checkBalance($this->from, $this->num);

            //验证该币种状态是否正常
            $balanceModel->canChange();

            $count = $balanceModel
                ->where('id', $balanceModel->id)
                ->where('balance', '>=', $this->num)
                ->decrement('balance', $this->num);

            if (!$count) {
                throw new ModelSaveException(trans('coin::exception.余额操作(减少)失败'));
            }

            $balanceModel->balance -= $this->num;
            $balanceModel->syncOriginalAttribute('balance');

            $return['fromLog'] = $this->recordToLog([
                'user_id' => $this->from,
                'symbol' => $this->symbol,
                'no' => $this->no,
                'num' => -$this->num,
                'module' => $this->module,
                'action' => $this->action,
                'info' => $this->info ?: new TranslateExpression('coin::message.转入')
            ]);

            $this->ensureLogRight($this->from, $balanceModel->balance);
        }

        if ($this->to) { // 增加
            $return['to'] = $balanceModel = $this->getBalance($this->to);

            $count = $balanceModel->increment('balance', $this->num);

            if (!$count) {
                throw new ModelSaveException(trans('coin::exception.余额操作(增加)失败'));
            }

            $return['toLog'] = $this->recordToLog([
                'user_id' => $this->to,
                'symbol' => $this->symbol,
                'no' => $this->no,
                'num' => $this->num,
                'module' => $this->module,
                'action' => $this->action,
                'info' => $this->info ?: new TranslateExpression('coin::message.转入')
            ]);

            //确保入账畅通
            //$this->ensureLogRight($this->to, $balanceModel->balance);
        }

        //event(new UserCoinBalanceChanged($this, $return));

        return $return;
    }

    protected function checkFromAndTo()
    {
        if (empty($this->from) && empty($this->to)) {
            throw new InvalidArgumentException(trans('coin::exception.请补充余额操作对象'));
        }

        $this->checkToSelf();
    }

    protected function checkToSelf()
    {
        if ($this->from == $this->to) {
            throw new InvalidArgumentException(trans('coin::exception.不能转账给自己'));
        }
    }

    protected function checkSymbol()
    {
        if (empty($this->symbol)) {
            throw new InvalidArgumentException(trans('coin::exception.请补充转账币种'));
        }
    }

    protected function checkNum()
    {
        if (empty($this->num)) {
            throw new InvalidArgumentException(trans('coin::exception.请补充转账数量'));
        }
    }

    /**
     * 确认转出用户是否实名认证
     */
    protected function ensureUserAuth($userId)
    {
        return with_user($userId)->isAuthVerified();
    }


    /**
     * @param $userId
     * @param $value
     *
     * @return CoinAsset
     */
    protected function checkBalance($userId, $value)
    {
        $balanceModel = $this->getBalance($userId);

        if ($balanceModel->balance < $value) {
            throw new InvalidArgumentException(trans('coin::exception.余额不足'));
        }

        return $balanceModel;
    }

    /**
     * @param $userId
     *
     * @return CoinAsset
     */
    protected function getBalance($userId)
    {
        $balanceModel = CoinAsset::firstOrCreate([
            'user_id' => $userId,
            'symbol' => $this->symbol,
        ]);

        return $balanceModel;
    }

    private $logService;

    protected function logService(): CoinLogService
    {
        if ($this->logService === null) {
            $this->logService = resolve(CoinLogService::class);
        }

        return $this->logService;
    }

    /**
     * @param $data
     * @param array $options
     *
     * @return bool|\Illuminate\Database\Eloquent\Model
     * @throws ModelSaveException
     */
    protected function recordToLog($data, array $options = [])
    {
        return $this->logService()->create($data, $options);
    }

    /**
     * 确认用户的余额日志是否正确
     *
     * @param User|int $user
     * @param $value
     *
     * @throws CoinLogBadDebtException
     */
    protected function ensureLogRight($user, $value, array $options = [])
    {
        $sum = $this->logService()->getSymbolSumByUser($user, $this->symbol);

        if (bccomp($sum, $value) !== 0) {
            event(new UserCoinLogBadDebt(with_user($user), $this->symbol, $sum, $value));
            if ($options['exception'] ?? true) {
                throw new CoinLogBadDebtException($this);
            }
        }
        return false;
    }
}
