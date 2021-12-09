<?php

namespace Modules\Coinv2\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Core\Services\Frontend\UserService;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;
use Modules\Mttl\Services\EnergyCardBuyService;
use Modules\User\Http\Controllers\admin\AutomaticController;

class WithdrawService
{
    use HasQuery {
        create as queryCreate;
    }

    /**
     * @var CoinWithdraw
     */
    protected $model;

    /**
     * RechargeService constructor.
     *
     * @param TradeService $tradeService
     */
    public function __construct(CoinWithdraw $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $data
     * @param array $options
     *
     * @return bool|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Modules\Coinv2\Exceptions\CoinException
     * @throws \Throwable
     */
    public function create(array $data, array $options = [])
    {
        if ($options['transaction'] ?? true) {
            return DB::transaction(function () use ($data, $options) {
                // 内部不需要事务
                $options['transaction'] = false;
                $options['checkPayPassword'] = false;
                $options['changeBalanceOptions'] = array_merge(
                    $options['changeBalanceOptions'] ?? [],
                    ['transaction' => false]
                );

                return $this->creating($data, $options);
            });
        }

        return $this->creating($data, $options);
    }

    /**
     * @param array $data
     * @param array $options
     *
     * @return bool|\Illuminate\Database\Eloquent\Model
     * @throws \Modules\Coinv2\Exceptions\CoinException
     */
    protected function creating(array $data, array $options = [])
    {


        $user = with_user($data['user_id']);

        $buyService=resolve(EnergyCardBuyService::class);
        $buyService->isBuy($data['user_id']);

        $userService = resolve(UserService::class);

        if ($options['checkPayPassword'] ?? true) {
            $userService->checkPayPassword($user, $data['pay_password']);
        }

        /** @var CoinService $coinService */
        $coinService = resolve(CoinService::class);
        $coin = $coinService->getBySymbol($data['symbol'], $options['coinOptions'] ?? []);

        //验证钱包地址正确性,多个用户通过不了验证，取消验证
//        if ($user->id == 372 || $user->id == 1676 || $user->id == 41) {
//
//        } else {
//            $coinService->checkAddress($data['chain'], $data['to'], true);
//        }

        $coin->canWithdrawWith($data['num']);
        $cost = $coin->calcWithdrawFeeWith($data['num']);

        //判断该用户币种资产释放单独冻结
        $coinAssetService = resolve(AssetService::class);
        $coinAsset = $coinAssetService->one([
            'user_id' => $data['user_id'],
            'symbol' => $data['symbol'],
            'status' => 1,
            'frozen' => 0,
        ], [
            'exception' => false
        ]);
        if (empty($coinAsset)) {
            throw new \Exception($data['symbol'] . trans('coin::exception.已冻结不能提现'));
        }


        //根据设置的提现状态(影响是否直接写入trade表)
        // 提现状态：0=暂停提现、1=全部需要人工审核、2=走系统设置
        $withdrawState = 0;
        switch ($coin->withdraw_state) {
            case 1:
                $withdrawState = CoinWithdraw::STATE_HUMAN_TRANSFER;
                break;
            case 2:
                $withdrawState = CoinWithdraw::STATE_PROCESSING;
                break;
            default:
                break;
        }
        if ($withdrawState == 0) {
            throw new \Exception(trans('coin::exception.该币种暂停提现'));
        }

       /* if ($cost['num']<=500){
            $withdrawState=CoinWithdraw::STATE_PROCESSING;
        }*/

        // 一周只能提现两次
        /*$weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $week_count = CoinWithdraw::query()->where('symbol', $data['symbol'])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->where('state', '>', -3)
            ->count();
        if ($week_count >= 2) {
            throw new \Exception(trans('coin::exception.一周仅能提款两次'));
        }*/

        //一天只能提现一次
        $weekStart=date('Y-m-d');
        $weekEnd=date('Y-m-d',strtotime($weekStart)+86400);
        $week_count = CoinWithdraw::query()->where('symbol', $data['symbol'])
            ->where('user_id', $user->id)
            ->where('created_at','>=',$weekStart)
            ->where('created_at','<',$weekEnd)
            ->where('state', '>', -3)
            ->count();
        if ($week_count >= 1) {
            throw new \Exception(trans('coin::exception.一天仅能提款一次'));
        }

        $model = $this->queryCreate([
            'user_id' => $user->id,
            'to' => $data['to'],
            'symbol' => $data['symbol'],
            'num' => $cost['num'],
            'state' => $withdrawState, //CoinWithdraw::STATE_PROCESSING,
            'cost' => $cost['fee'],
            'chain' => $data['chain']
        ], $options);

        if ($options['changeBalanceOptions'] ?? true) { // 修改余额
            $balanceChangeService = resolve(BalanceChangeService::class);
            $changeBalanceOptions = $options['changeBalanceOptions'] ?? [];
            $balanceChangeService
                ->from($model->user_id)
                ->withSymbol($model->symbol)
                ->withNum($data['num'])
                ->withModule('coin.withdraw_to')
                ->withNo($model->id)
                ->withInfo(
                    $changeBalanceOptions['info'] ??
                    new TranslateExpression('coin::message.提现扣除', ['num' => $model->num, 'cost' => $model->cost])
                )
                ->change($changeBalanceOptions);
        }

        return $model;
    }
}
