<?php


namespace Modules\Mttl\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Coin\Services\BalanceChangeService;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;
use Modules\Mttl\Jobs\DynamicRewardJob;
use Modules\Mttl\Models\EnergyCard;
use Modules\Mttl\Models\EnergyCardBuy;
use Modules\User\Models\BuyLog;
use Modules\User\Models\ProjectUser;
use Throwable;

class EnergyCardBuyService
{
    use HasQuery;

    public function __construct(EnergyCardBuy $model)
    {
        $this->model = $model;
    }

    /**
     * 购买能量卡
     * @param Model $user 用户实体
     * @param Model $card 能量卡实体
     * @param boolean $automatic 开启自动购买
     * @param array $options
     * @return bool|Model|mixed|null
     * @throws
     * @throws Throwable
     */
    public function add($user, $card, $automatic = false, $options = [])
    {
        if ($options['transaction'] ?? false) {
            // 默认不开启事务, 但是需注意事务导致的数据一致性
            return DB::transaction(function () use ($user, $card, $automatic) {
                return $this->adding($user, $card->toArray(), $automatic);
            });
        }

        return $this->adding($user, $card->toArray(), $automatic);
    }

    public function adding($user, $data, $automatic)
    {
        $record = $this->added(with_user_id($user), $data);
        // 变更自动购买设置
        if ($automatic) {
            $service = resolve(EnergyCardAutomaticService::class);
            $service->change($user, $data, $automatic);
        }
        return $record;
    }

    /**
     * 添加能量卡购买记录
     * @param integer $user_id
     * @param array $data
     * @param bool $isAutomatic
     * @return bool|Model|null
     * @throws ModelSaveException
     * @throws Throwable
     */
    public function added($user_id, $data, $isAutomatic = false)
    {
        // 检查一下用户当日是否已购买能量卡
        $todayCount = $this->model->newQuery()
            ->whereDate('created_at', date('Y-m-d'))
            ->where('user_id', $user_id)
            ->lockForUpdate()
            ->count();
        if ($todayCount) {
            if (!$isAutomatic)
                throw new \Exception(trans('mttl::exception.每日只可购买一张能量卡'));
            else
                return null;
        }

        $user = ProjectUser::query()->where('user_id', $user_id)->first();
        // 检查一下是否有当前权限可以购买的相同本金的能量卡
        $count = EnergyCard::query()
            ->withTrashed()
            ->where('principal', $data['principal'])
            ->whereJsonContains('buyable_level', $user->type)
            ->count();
        if (!$count) {
            return null;
        }

        $model = $this->create([
            'user_id' => $user_id,
            'types' => $data['types'],
            'principal' => $data['principal'],
            'daily_rate' => $data['daily_rate'],
            'total_days' => $data['total_days'],
            'issued_days' => 0,
            'surplus_days' => $data['total_days'],
            'begin_date' => date('Y-m-d', strtotime('+1 day')) . ' 00:00:00',
            'finish_date' => date('Y-m-d', strtotime("+{$data['total_days']} day")) . ' 23:59:59',
            'automatic' => $isAutomatic,
        ]);

        // 变更余额
        $service = resolve(BalanceChangeService::class);
        $service->from($user_id)
            ->withNum($data['principal'])
            ->withSymbol('USDT')
            ->withNo($model->id)
            ->withInfo(new TranslateExpression('mttl::message.购买能量卡'))
            ->withModule('mttl.buy_card')
            ->change();

        BuyLog::addLog($user_id,$data['principal'].'USDT',1);
        // 1分钟后队列发放动态奖励
        DynamicRewardJob::dispatch($model)->delay(now()->addMinutes(1));

        return $model;
    }

    /**
     * 我的能量卡
     * @param $user
     * @return LengthAwarePaginator
     */
    public function my($user)
    {
        return $this->paginate(
            [
                ['user_id', '=', with_user_id($user)],
                ['surplus_days', '>', 0]
            ],
            [
                'orderBy' => ['created_at', 'desc']
            ]
        );
    }

    /**
     * 获取释放中的能量卡数量
     * @param integer $user_id
     * @return int
     */
    public function onlineCount($user_id)
    {
        return $this->count([
            ['user_id', '=', $user_id],
            ['surplus_days', '>', 0]
        ]);
    }

    /**
     * 是否购买
     * @param $uid
     * @throws \Exception
     */
    public function isBuy($uid){
        $model= $this->one(['user_id' =>$uid], ['exception' => false]);
        $authority=ProjectUser::query()->where('user_id',$uid)->value('authority');
        if (empty($model)&&empty($authority)) throw new \Exception(trans('mttl::exception.转换能量矩阵激活此功能'));
        if ($authority['empty']<1&&empty($model))  throw new \Exception(trans('mttl::exception.转换能量矩阵激活此功能'));
    }

    /**
     * 是否开启链接权限
     * @param $uid
     * @throws \Exception
     */
    public function isCheck($uid){
        $list= ProjectUser::query()->where('user_id',$uid)->select('check','check_num')->first();
        if ($list['check']==2&&$list['check_num']>0){
            throw new \Exception(trans('mttl::exception.未有能力进入此维度'));
        }
    }
}
