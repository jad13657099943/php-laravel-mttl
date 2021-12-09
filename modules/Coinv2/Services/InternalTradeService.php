<?php

namespace Modules\Coinv2\Services;

use Modules\Coin\Models\Coin;
use Modules\Coin\Models\CoinInternalTrade;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Services\Frontend\UserService;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;

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

        $userService = resolve(UserService::class);

        if ($options['checkPayPassword'] ?? true) {
            $userService->checkPayPassword($user, $data['pay_password']);
        }

        //验证接收会员是否存在
        $toUser = $userService->getById($data['to'], [
            'exception' => false,
        ]);
        if (empty($toUser)) {
            throw new \Exception('转入会员不存在');
        }

        $coinState = Coin::query()->where('symbol', $data['symbol'])->value('internal_state');
        if (empty($coinState)) {
            throw new \Exception('该币种未开启内部转账');
        }

        return \DB::transaction(function () use ($user, $data) {

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
                        ['to' => with_user_id($data['to']), 'num' => $data['num'], 'symbol' => $data['symbol']]
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
                        ['from' => with_user_id($data['user_id']), 'num' => $data['num'], 'symbol' => $data['symbol']]
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
