<?php

namespace Modules\Coinv2\Services;

use Modules\Coin\Models\CoinExchangeOrder;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Services\Frontend\UserService;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;

class ExchangeService
{
    use HasQuery;

    protected $model;

    public function __construct(CoinExchangeOrder $model)
    {
        $this->model = $model;
    }

    public function create(array $data, array $options = [])
    {
        $user = with_user($data['user']);
        $data['coin_pay'] = strtoupper($data['coin_pay']);
        $data['coin_get'] = strtoupper($data['coin_get']);
        $data['user_id'] = $user->id;


        $userService = resolve(UserService::class);
        if ($options['checkPayPassword'] ?? true) {
            $userService->checkPayPassword($user, $data['pay_password']);
        }

        //获取设置交易对信息
        $settingService = resolve(ExchangeSettingsService::class);
        $info = $settingService->one([
            'coin_pay' => $data['coin_pay'],
            'coin_get' => $data['coin_get'],
            'status' => 1
        ], [
            'exception' => false
        ]);
        if (empty($info)) {
            throw new \Exception(trans('coin::exception.该兑换对信息未设置'));
        }

        //获取兑换比例
        $ratioData = $this->exchangeRatio($data['coin_pay'], $data['coin_get']);
        $ratio = $ratioData['ratio'];

        if ($ratio <= 0) {
            throw new \Exception(trans('coin::exception.兑换比例有误无法兑换'));
        }
        if ($info->ratio_max && $ratio >= $info->ratio_max) {
            throw new \Exception(trans('coin::exception.兑换比例已超过最大设置值'));
        }

        $getAmount = bcmul($data['pay_amount'], $ratio, 4);
        $payAmountMin = $info->pay_amount_min ?? 0;
        if ($getAmount < $payAmountMin) {
            throw new \Exception(trans('coin::exception.兑换得到数量不能小于') . $payAmountMin);
        }

        //判断手续费
        if($info->cost_ratio<0 || $info->cost_ratio>=100){
            throw new \Exception(trans('coin::exception.兑换手续费参数设置错误'));
        }
        $cost = 0;
        if($info->cost_ratio>0){
            $cost = bcmul($getAmount,$info->cost_ratio/100,4);
            $getAmount = bcsub($getAmount,$cost,4);
        }


        $order = [
            'user_id' => $data['user_id'],
            'coin_pay' => $data['coin_pay'],
            'coin_get' => $data['coin_get'],
            'pay_amount' => abs($data['pay_amount']),
            'get_amount' => abs($getAmount),
            'cost' => $cost,
            'cost_rate' => $ratio,
            'ratio_at' => date('Y-m-d H:i:s'),
            'status' => CoinExchangeOrder::STATE_SUCCESS,
        ];

        return \DB::transaction(function () use ($order) {

            $model = new CoinExchangeOrder($order);
            $model->save();

            //增加
            $balanceChangeService = resolve(BalanceChangeService::class);
            $balanceChangeService->to($order['user_id'])
                ->withSymbol($order['coin_get'])
                ->withNum($order['get_amount'])
                ->withModule('coin.exchange_inc')
                ->withNo($model->id)
                ->withInfo(
                    new TranslateExpression('coin::message.兑换增加')
                )->change();

            //减少
            $balanceChangeService = resolve(BalanceChangeService::class);
            $balanceChangeService->from($order['user_id'])
                ->withSymbol($order['coin_pay'])
                ->withNum($order['pay_amount'])
                ->withModule('coin.exchange_dec')
                ->withNo($model->id)
                ->withInfo(
                    new TranslateExpression('coin::message.兑换减少')
                )->change();

            return $model;
        });

    }


    /**
     * 获取兑换比例
     * 前提是获取币价那里要计算好币种对的兑换比例
     * @param $payCoin
     * @param $getCoin
     * @return int[]
     */
    public function exchangeRatio($payCoin, $getCoin)
    {

        $payCoin = strtoupper($payCoin);
        $getCoin = strtoupper($getCoin);
        //获取btc、eth等币种价格
        $coinService = resolve(CoinService::class);
        $coinPrice = $coinService->syncPrice();
        $coinPrice = $coinPrice->toArray();


        $radio = 0;
        if (isset($coinPrice[$payCoin])) {
            $data = $coinPrice[$payCoin];
            if (isset($data[$getCoin])) {
                $radio = $data[$getCoin];
            }
        }

        if ($radio <= 0) {

            throw new \Exception(trans('coin::exception.获取兑换比例失败', [
                'pay_coin' => $payCoin,
                'get_coin' => $getCoin,
            ]));
        }

        return [
            'ratio' => $radio
        ];
    }


}
