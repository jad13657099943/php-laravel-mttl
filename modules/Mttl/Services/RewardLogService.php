<?php


namespace Modules\Mttl\Services;

use Illuminate\Support\Facades\Log;
use Modules\Coin\Services\BalanceChangeService;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;
use Modules\Mttl\Models\EnergyCardAutomatic;
use Modules\Mttl\Models\EnergyCardBuy;
use Modules\Mttl\Models\EnergyCardReward;
use Modules\Mttl\Models\RewardLog;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\ProjectUserService;
use InvalidArgumentException;

class RewardLogService
{
    use HasQuery;

    public function __construct(RewardLog $model)
    {
        $this->model = $model;
    }

    /**
     * 发放推荐奖励
     * @param ProjectUser $user
     * @throws
     */
    public function referralReward($user)
    {
        $service = resolve(ProjectUserService::class);
        $pidAll = $service->getUserPidAll($user->user_id);
        if (!$pidAll) return;
        $pid = $pidAll[0];

        $pidModel = ProjectUser::query()->where('user_id', $pid)->first();
        // 精神领袖才会发放奖励
        if ($pidModel->type != 1) return;

        $reward_amount = config('user::config.reward_recommend', 300);
        // 发放奖励
        $model = $this->create([
            'user_id' => $pid,
            'no' => 0,
            'symbol' => 'USDT',
            'amount' => $reward_amount,
            'type' => RewardLog::TYPE_RECOMMEND,
            'msg' => new TranslateExpression('mttl::message.推荐奖励'),
            'algebra' => 1,
            'source_user' => $user->user_id,
            'source_show_userid' => $user->show_userid
        ]);

        $balanceService = resolve(BalanceChangeService::class);
        $balanceService->to($pid)
            ->withNum($reward_amount)
            ->withSymbol('USDT')
            ->withNo($model->id)
            ->withInfo(new TranslateExpression('mttl::message.推荐奖励'))
            ->withModule('mttl.referral_reward')
            ->change();
    }

    /**
     * 动态奖励（集结奖励，级别奖励，平级奖励）
     * @param EnergyCardBuy|mixed $cardBuy
     * @throws
     */
    public function dynamicReward($cardBuy)
    {
        $assemblyArray = [
            ['min' => 1, 'max' => 5, 'ratio' => bcdiv(config('user::config.gather_1_5_reward', 0.3), 100, 4)],
            ['min' => 6, 'max' => 10, 'ratio' => bcdiv(config('user::config.gather_6_10_reward', 0.09), 100, 4)],
            ['min' => 11, 'max' => 20, 'ratio' => bcdiv(config('user::config.gather_11_20_reward', 0.03), 100, 4)]
        ];

        $service = resolve(ProjectUserService::class);

        $user_id = $cardBuy->user_id;
        if ($cardBuy->surplus_days !== $cardBuy->total_days) return;

        $pidAll = $service->getUserPidAll($user_id);
        $user = ProjectUser::query()->where('user_id', $user_id)->first();
        $parent = [];

        \DB::transaction(function () use ($assemblyArray, $pidAll, $cardBuy, $user, &$parent) {
            $cardBuyService = resolve(EnergyCardBuyService::class);
            $userService = resolve(ProjectUserService::class);
            foreach ($pidAll as $key => $pid) {
                $pidUser = ProjectUser::query()->where('user_id', $pid)->first();
                // 判断是否同一个团队下面的人
                if (
                    !is_numeric(substr($user->show_userid, 0, 1))
                    && substr($user->show_userid, 0, 1) != substr($pidUser->show_userid, 0, 1)
                ) {
                    continue;
                }
                // 释放中的能量卡数量
                $cardCount = $cardBuyService->onlineCount($pid);
                // 动态奖励权限
                $authority = $userService->checkAuthority($pidUser, ProjectUser::AUTHORITY_DYNAMIC);
                // 空单发放动态奖励权限
                $emptyAuthority = $userService->checkAuthority($pidUser, ProjectUser::AUTHORITY_EMPTY);
                // 会员等级
                $userGrade = ProjectUser::query()->where('user_id', $pid)->value('grade');
                if ($userGrade > 0 && ($cardCount > 0 || $emptyAuthority) && $authority) {
                    $parent[] = array('user_id' => $pid, 'vip' => $userGrade);
                }

                // 集结奖励最多发放20代，或无释放中的能量卡，或无动态奖励权限
                if ($key > 19 || ($cardCount == 0 && !$emptyAuthority) || !$authority) continue;

                $algebra = $key + 1;
                $level = ceil($algebra / 5) - 1;
                $level = $level >= 3 ? 2 : $level;
                $ratio = $assemblyArray[$level]['ratio'];
                $amount = bcmul($cardBuy->principal, $ratio, 4);

                // 发放集结奖励
                $model = $this->create([
                    'user_id' => $pid,
                    'no' => $cardBuy->id,
                    'symbol' => 'USDT',
                    'amount' => $amount,
                    'type' => RewardLog::TYPE_ASSEMBLY,
                    'msg' => new TranslateExpression('mttl::message.集结奖励'),
                    'algebra' => $algebra,
                    'source_user' => $user->user_id,
                    'source_show_userid' => $user->show_userid,
                    'extra' => [
                        'card_amount' => $cardBuy->principal
                    ]
                ]);

                $balanceService = resolve(BalanceChangeService::class);
                $balanceService->to($pid)
                    ->withNum($amount)
                    ->withSymbol('USDT')
                    ->withNo($model->id)
                    ->withInfo(new TranslateExpression('mttl::message.集结奖励'))
                    ->withModule('mttl.assembly_reward')
                    ->change();
            }

            if (empty($parent)) return;


            //获取各个等级对应的极差比例
            $config['jicha_1'] = config('user::config.level_1_reward', 1);
            $config['jicha_2'] = config('user::config.level_2_reward', 2);
            $config['jicha_3'] = config('user::config.level_3_reward', 3);
            $config['jicha_4'] = config('user::config.level_4_reward', 4);
            $config['jicha_5'] = config('user::config.level_5_reward', 5);
            $jichaUser = [];
            $pingjiUser = [];
            $temp_vip = 0;

            for ($x = 0; $x < count($parent); ++$x) {
                if ($parent[$x]['vip'] > $temp_vip) {
                    $jichaUser[] = array('user_id' => $parent[$x]['user_id'], 'vip' => $parent[$x]['vip']);
                    $temp_vip = $parent[$x]['vip']; //极差会员
                } else {
                    //平级会员(只要不是享受极差的，并且等级大于0的，都可以拿平级奖，平级奖每个等级只发放离自己最近的一人)
                    if ($parent[$x]['vip'] == $temp_vip && $temp_vip > 0) {
                        if (!in_array($temp_vip, array_column($pingjiUser, 'vip'))) {
                            $pingjiUser[] = [
                                'user_id' => $parent[$x]['user_id'],
                                'vip' => $parent[$x]['vip']
                            ];
                        }
                    }
                }
            }


            $arr = [];
            $temp_yina = 0;  //已拿
            for ($x = 0; $x < count($jichaUser); ++$x) {
                switch ($jichaUser[$x]['vip']) {
                    case '1':
                        $yuanben = $config['jicha_1'] ?? 0;
                        break;
                    case '2':
                        $yuanben = $config['jicha_2'] ?? 0;
                        break;
                    case '3':
                        $yuanben = $config['jicha_3'] ?? 0;
                        break;
                    case '4':
                        $yuanben = $config['jicha_4'] ?? 0;
                        break;
                    case '5':
                        $yuanben = $config['jicha_5'] ?? 0;
                        break;

                    default:
                        $yuanben = 0;
                        break;
                }
                $yina = $yuanben - $temp_yina; //应该拿多少
                $temp_yina = $yuanben;
                $arr[] = [
                    'user_id' => $jichaUser[$x]['user_id'],
                    'bili' => $yina,
                    'vip' => $jichaUser[$x]['vip']
                ];
            }

//            print_r($jichaUser);
//            print_r($pingjiUser);
//            exit();

            // 发放极差奖励
            if ($arr) {
                foreach ($arr as $key => $value) {
                    $reward = bcmul(($value['bili'] / 100), $cardBuy->principal, 4);

                    // 发放极差奖励
                    $model = $this->create([
                        'user_id' => $value['user_id'],
                        'no' => $cardBuy->id,
                        'symbol' => 'USDT',
                        'amount' => $reward,
                        'type' => RewardLog::TYPE_LEVEL,
                        'msg' => new TranslateExpression('mttl::message.级别奖励'),
                        'algebra' => 0,
                        'source_user' => $user->user_id,
                        'source_show_userid' => $user->show_userid,
                        'extra' => [
                            'max_level' => $user->grade,
                            'card_amount' => $cardBuy->principal
                        ]
                    ]);

                    $balanceService = resolve(BalanceChangeService::class);
                    $balanceService->to($value['user_id'])
                        ->withNum($reward)
                        ->withSymbol('USDT')
                        ->withNo($model->id)
                        ->withInfo(new TranslateExpression('mttl::message.级别奖励'))
                        ->withModule('mttl.level_reward')
                        ->change();

                    // 发放平级奖励
                    $pingji_key = array_search($value['vip'], array_column($pingjiUser, 'vip'));
                    if ($pingji_key !== false) {
                        $pingji_value = $pingjiUser[$pingji_key];
                        // 平级奖励为该级别的极差奖励的20%
                        $pingji_reward = bcmul(
                            $reward,
                            bcdiv(config('user::config.peer_reward', 20), 100, 4),
                            4
                        );
                        if ($pingji_reward < 0.0001) continue;
                        $model = $this->create([
                            'user_id' => $pingji_value['user_id'],
                            'no' => $cardBuy->id,
                            'symbol' => 'USDT',
                            'amount' => $pingji_reward,
                            'type' => RewardLog::TYPE_PEER,
                            'msg' => new TranslateExpression('mttl::message.平级奖励'),
                            'algebra' => 0,
                            'source_user' => $user->user_id,
                            'source_show_userid' => $user->show_userid,
                            'extra' => [
                                'peer_level' => $pingji_value['vip'],
                                'level_reward' => $reward,
                                'card_amount' => $cardBuy->principal
                            ]
                        ]);

                        $balanceService = resolve(BalanceChangeService::class);
                        $balanceService->to($pingji_value['user_id'])
                            ->withNum($pingji_reward)
                            ->withSymbol('USDT')
                            ->withNo($model->id)
                            ->withInfo(new TranslateExpression('mttl::message.平级奖励'))
                            ->withModule('mttl.peer_reward')
                            ->change();
                    }
                }
            }
        });
    }

    /**
     * 静态收益
     * @param EnergyCardBuy $cardBuy
     * @throws
     */
    public function staticReward($cardBuy)
    {
        // 当前时间 - 最后发放时间如果小于24小时
        if (
            $cardBuy->last_releast_time &&
            (time() - strtotime($cardBuy->last_releast_time)) / 3600 <= 24
        ) {
            return;
        }

        if ($cardBuy->surplus_days <= 0) return;

        // 检查静态收益权限
        $userService = resolve(ProjectUserService::class);
        $user = ProjectUser::query()->where('user_id', $cardBuy->user_id)->first();
        $authority = $userService->checkAuthority($user, ProjectUser::AUTHORITY_STATIC);
        if (!$authority) return;

        try {
            \DB::transaction(function () use ($cardBuy) {
                // 收益金额
                $reward = bcmul($cardBuy->principal, $cardBuy->daily_rate, 4);

                $cardBuy->issued_days = $cardBuy->issued_days + 1;
                $cardBuy->surplus_days = $cardBuy->surplus_days - 1;
                $cardBuy->issued_amount = bcadd($cardBuy->issued_amount, $reward, 4);
                $cardBuy->last_releast_time = date('Y-m-d 00:00:00');
                $cardBuy->save();

                // 之前是需要审核，现在不需要。所以状态为1
                $rewardModel = new EnergyCardReward();
                $rewardModel->buy_record_id = $cardBuy->id;
                $rewardModel->user_id = $cardBuy->user_id;
                $rewardModel->principal = $cardBuy->principal;
                $rewardModel->daily_rate = $cardBuy->daily_rate;
                $rewardModel->amount = $reward;
                $rewardModel->state = 1;
                $rewardModel->save();

                // 发放奖励
                $model = $this->create([
                    'user_id' => $cardBuy->user_id,
                    'no' => $cardBuy->id,
                    'symbol' => 'USDT',
                    'amount' => $reward,
                    'type' => RewardLog::TYPE_INCREASE,
                    'msg' => new TranslateExpression('mttl::message.能量增幅'),
                    'extra' => [
                        'card_amount' => $cardBuy->principal,
                        'type' => $cardBuy->types
                    ]
                ]);

                $balanceService = resolve(BalanceChangeService::class);
                $balanceService->to($cardBuy->user_id)
                    ->withNum($reward)
                    ->withSymbol('USDT')
                    ->withNo($model->id)
                    ->withInfo(new TranslateExpression('mttl::message.能量增幅'))
                    ->withModule('mttl.energy_gain')
                    ->change();
            });
        } catch (\Exception $e) {
            Log::alert('静态收益发放失败', ['e' => $e, 'cardBuy' => $cardBuy]);
        }

        // 自动购买
        $automaticModel = EnergyCardAutomatic::query()
            ->where('user_id', $cardBuy->user_id)
            ->first();

        if ($automaticModel && $automaticModel->automatic) {
            $service = resolve(EnergyCardBuyService::class);
            \DB::beginTransaction();
            try {
                $service->added($cardBuy->user_id, $automaticModel->toArray(), true);
                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                Log::alert('自动购买能量卡失败', ['e' => $e, 'cardBuy' => $cardBuy, 'automaticModel' => $automaticModel]);
            }
        }
    }
}
