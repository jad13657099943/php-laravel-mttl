<?php

namespace Modules\Coin\Services;

use Modules\Coin\Models\Coin;
use Modules\Coin\Models\CoinInternalTrade;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Services\Frontend\UserService;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;
use Modules\Mttl\Services\EnergyCardBuyService;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\ProjectUserService;

class InternalTradeService
{
    use HasQuery;

    /**
     * @var CoinInternalTrade
     */
    protected $model;

    public function __construct(CoinInternalTrade $model)
    {
        $this->model = $model;
    }

    public function create(array $data, array $options = [])
    {
        $user = with_user($data['user_id']);

        $userService = resolve(ProjectUserService::class);

        $fromUser = $userService->getByUserid($user->id, ['exception' => false]);
        if (empty($fromUser)) {
            throw new \Exception('账户异常');
        }

        $buyService=resolve(EnergyCardBuyService::class);
        $buyService->isBuy($user->id);
        $buyService->isCheck($user->id);

        if (!$userService->checkAuthority($fromUser, ProjectUser::AUTHORITY_TRANSFER)) {
            throw new \Exception('无法使用此功能！');
        }

        if ($options['checkPayPassword'] ?? true) {
            $userService->checkPayPassword($user, $data['pay_password']);
        }

        //验证接收会员是否存在
        $toUser = $userService->getByShowUserid($data['to'], [
            'exception' => false,
        ]);
        if (empty($toUser)) {
            throw new \Exception('转入会员不存在');
        }

        if (
            !is_numeric(substr($fromUser->show_userid, 0, 1))
            && substr($fromUser->show_userid, 0, 1) !== substr($toUser->show_userid, 0, 1)
        ) {
            throw new \Exception('只能向团队内部成员转账');
        }

        // 赋值为user_id
        $data['to'] = $toUser->user_id;

        $coinState = Coin::query()->where('symbol', $data['symbol'])->value('internal_state');
        if (empty($coinState)) {
            throw new \Exception('该币种未开启内部转账');
        }

        return \DB::transaction(function () use ($user, $data, $fromUser, $toUser) {

            $data['symbol'] = strtoupper($data['symbol']);
            $model = new CoinInternalTrade([
                'from_user_id' => $user->id,
                'to_user_id' => $data['to'],
                'symbol' => $data['symbol'],
                'number' => abs($data['num']),
                'get_num' => abs($data['num']),
                'fee' => 0, //未考虑手续费
            ]);
            if (!$model->save()) {
                throw ModelSaveException::withModel($model);
            }


            //减少转出会员资产
            $balanceChangeService = resolve(BalanceChangeService::class);
            $balanceChangeService
                ->from($data['user_id'])
                ->withSymbol($data['symbol'])
                ->withNum($data['num'])
                ->withModule('coin.withdraw_to')
                ->withNo($model->id)
                ->withInfo(
                    new TranslateExpression(
                        'coin::message.内部转账扣除',
                        ['to' => $toUser->show_userid, 'num' => $data['num'], 'symbol' => $data['symbol']]
                    )
                )
                ->change();

            //增加转出会员资产
            $balanceChangeService = resolve(BalanceChangeService::class);
            $balanceChangeService
                ->to($data['to'])
                ->withSymbol($data['symbol'])
                ->withNum($data['num'])
                ->withModule('coin.withdraw_to')
                ->withNo($model->id)
                ->withInfo(
                    new TranslateExpression(
                        'coin::message.内部转账增加',
                        ['from' => $fromUser->show_userid, 'num' => $data['num'], 'symbol' => $data['symbol']]
                    )
                )
                ->change();

            return $model;
        });
    }


    /**
     * 返回内部转账双方会员
     * @param $id
     */
    public function infoUsers($id)
    {

        $info = $this->getById($id);
        $fromUser = with_user($info->from_user_id);
        $toUser = with_user($info->to_user_id);
        return [
            'from_user' => $fromUser,
            'to_user' => $toUser
        ];
    }
}
