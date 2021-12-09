<?php

namespace Modules\Otc\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Coin\Services\BalanceChangeService;
use Modules\Coin\Services\CoinLogModuleService;
use Modules\Core\Exceptions\Frontend\Auth\UserBankException;
use Modules\Core\Exceptions\Frontend\Auth\UserPayPasswordException;
use Modules\Core\Models\Frontend\UserBank;
use Modules\Core\Services\Frontend\UserBankService;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;
use Modules\Otc\Events\TradeConfirmed;
use Modules\Otc\Events\TradePaid;
use Modules\Otc\Exceptions\OtcTradeException;
use Modules\Otc\Jobs\TradeExpiredJob;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Models\OtcUser;
use Throwable;

class OtcTradeService
{
    use HasQuery {
        one as queryOne;
        create as queryCreate;
    }

    /**
     * @var OtcTrade
     */
    protected $model;

    public function __construct(OtcTrade $model)
    {
        $this->model = $model;
    }

    /**
     * @param $user
     * @param array $where
     * @param array $options
     * @return LengthAwarePaginator|Collection
     */
    public function allByWhere($user, array $where = [], $options = [])
    {
        $options['queryCallback'] = function ($query) use ($user, $where) {
            if (empty($where['type'])) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            } else {
                if ($where['type'] == OtcTrade::TYPE_BUY) {
                    $query->where('buyer_id', $user->id);
                } else {
                    $query->where('seller_id', $user->id);
                }
            }
        };
        $result = $this->all($where, array_merge([
            'paginate' => true,
            'orderBy' => ['id', 'desc']
        ], $options));
        return $result;
    }

    /**
     * @param $user
     * @param array $data
     * @param array $options
     * @return mixed
     * @throws OtcTradeException
     * @throws UserBankException
     * @throws \Modules\Core\Exceptions\Frontend\Auth\UserAuthVerifyException
     * @throws \Modules\Core\Exceptions\ModelSaveException
     * @throws \Modules\Otc\Exceptions\OtcExchangeException
     * @throws \Modules\Otc\Exceptions\OtcUserExchangeException
     */
    public function create($user, array $data, array $options = [])
    {

        $user = with_user($user);
        /** @var $otcUser OtcUser */
        resolve(OtcUserService::class)->firstOrCreate(['user_id' => $user->id]);

        $user->isAuthVerified();
        //校验交易密码
        $user->isPayPasswordSet();
        $user->checkPayPassword($data['pay_password'], true);

        $otcUserService = resolve(OtcUserService::class);
        $otcUser = $otcUserService->firstOrCreate(['user_id' => $user->id]);

        //检查订单是否可以进行撮合交易
        /** @var $otcExchange OtcExchange */
        $otcExchangeService = resolve(OtcExchangeService::class);
        $otcExchange = $otcExchangeService->one(['id' => $data['otc_exchange_id']]);
        $otcExchange->canTrade($data['num']);
        //当前用户是否能有购买资格
        $otcExchange->canTradeByOtcUser($otcUser);
        $bankService = resolve(UserBankService::class);

        if ($otcExchange->isBuy()) {
            //用户是否设置了支付卡
            $bankService->hasEnableBank($user, ['exception' => true]);
            $data['type'] = OtcExchange::TYPE_SELL;
            $data['buyer_id'] = $otcExchange->user_id;
            $data['seller_id'] = $user->id;
            //$data['bank_list'] = $bankService->enableBank($user->id)->pluck('bank');//$bankService->enableBankList($user->id)->keyBy('bank');

        } else {

            $data['type'] = OtcExchange::TYPE_BUY;
            $data['buyer_id'] = $user->id;
            $data['seller_id'] = $otcExchange->user_id;
            //$data['bank_list'] = $bankService->enableBank($otcExchange->user_id)->pluck('bank');//$bankService->enableBankList($otcExchange->user_id)->keyBy('bank');

        }

        $data['min'] = $otcExchange->min;
        $data['max'] = $otcExchange->max;
        $data['exchange_type'] = $otcExchange->type;
        $data['coin'] = strtoupper($otcExchange->coin);
        $data['price'] = $otcExchange->price;
        $data['status'] = OtcTrade::STATUS_WAITING;
        $data['expired_at'] = date('Y-m-d H:i:s', time() + config('otc::trade.expired_time', 15) * 60);
        $data['no'] = generate_otc_no(OtcTrade::class);

        /** @var OtcTrade $otcTrade */
        return \DB::transaction(function () use ($data, $otcExchange, $user) {

            /** @var OtcTrade $otcTrade */
            $otcTrade = $this->queryCreate($data);
            //冻结对应币种
            $otcTrade = $this->frozen($otcTrade);

            //更改挂单剩余可交易数量
            //todo 超卖问题
            \DB::table('otc_exchanges')
                ->where('surplus', '>=', $data['num'])
                ->where('id', $otcExchange->id)
                ->update([
                    'status' => OtcExchange::STATUS_SELLING,
                    'surplus' => \DB::raw('surplus - ' . $data['num'])
                ]);
            return $otcTrade;
        });
    }

    /**
     * 冻结对应币种
     *
     * @param $otcTrade
     * @param array $options
     * @return mixed
     * @throws OtcTradeException
     */
    public function frozen($otcTrade, $options = [])
    {
        $otcTrade = with_otc_trade($otcTrade);
        $otcTrade->isWaitingFrozen();
        return \DB::transaction(function () use ($otcTrade, $options) {

            if ($otcTrade->isBuy()) {
                //如果是撮合买入,挂单卖出，无需操作币种，因为在提交挂单卖出的时候，已经冻结了用户对应的币种
                $otcTrade->setFrozen()->saveOrFail();
            } else {
                //如果是撮合卖出，挂单买入,冻结卖出用户对应币种

                $module = 'otc.trade_buy_frozen';
                $balanceChangeService = resolve(BalanceChangeService::class);
                $balanceChangeService->from($otcTrade->seller_id)
                    ->withSymbol($otcTrade->coin)
                    ->withNum($otcTrade->num)
                    ->withModule($module)
                    //->withInfo(resolve(CoinLogModuleService::class)->title($module))
                    ->withInfo(new TranslateExpression('coin::message.撮合买入冻结资金'))
                    ->withNo($otcTrade->id)
                    ->change();
                //更改撮合订单状态
                $otcTrade->setFrozen()->saveOrFail();

            }
            return $otcTrade;
        });

    }

    /**
     * 买家付款
     *
     * @param $otcTrade
     * @param $user
     * @param $data
     * @param array $options
     * @return mixed
     * @throws OtcTradeException
     * @throws UserBankException
     */
    public function pay($otcTrade, $user, $data, $options = [])
    {
        $otcTrade = with_otc_trade($otcTrade);
        $user->isAuthVerified();
        //校验交易密码
        $user->isPayPasswordSet();
        $user->checkPayPassword($data['pay_password'], true);

        //下单资格在创建挂单时候已经校验
        //校验用户是否有当期订单的付款资格
        $otcTrade->canPayByUser($user);

        //校验卖家银行卡
        $bank = [
            'id' => $data['bank']['id'],
            'bank' => $data['bank']['bank'],
            'value' => $data['bank']['value'],
        ];
        $userBankService = resolve(UserBankService::class);
        $userBankService->checkBankExists(array_merge($bank,
            [
                'user_id' => $otcTrade->seller_id,
                'enable' => UserBank::ENABLE_OPEN
            ]
        ), ['exception' => true]);

        return \DB::transaction(function () use ($otcTrade, $user, $bank, $options) {
            //如果是卖出挂单，撮合买入，当前用户付款，需要用户转账支付微信，无需操作coin，确认支付后再操作coin
            $otcTrade->setPaid($bank)->saveIfFail();
            $otcTrade->refresh();
            event(new TradePaid($otcTrade));
            return $otcTrade;
        });

    }

    /**
     * 确认对方已经付款
     *
     * @param $otcTrade
     * @param $user
     * @param $payPassword
     * @param array $options
     * @return array
     * @throws OtcTradeException
     * @throws Throwable
     */
    public function confirm($otcTrade, $user, $payPassword, $options = [])
    {
        $user->checkPayPassword($payPassword, true);

        $otcTrade = with_otc_trade($otcTrade);

        if (!$otcTrade->canConfirmByUser($user)) {
            throw new OtcTradeException(trans('otc::exception.您无法确认该订单支付状态'));
        }

        if (!$otcTrade->isWaitingConfirm()) {
            //throw new OtcTradeException(trans('订单状态为' . $otcTrade->statusText . '，无法确认'));
            throw new OtcTradeException(trans('otc::exception.订单状态无法确定',['status'=>$otcTrade->statusText]));
        }
        //如果是买单，需要给买家增加对应币种,挂单为卖单，在创建挂单的时候，已经操作过coin钱包，所以这里无需操作卖方钱包
        //如果是卖单，需要给买家增加对应币种,挂单为买单，当前用户支付宝微信收款，此时确认需要给买方增加相应币种

        $otcTrade = \DB::transaction(function () use ($otcTrade) {
            $module = 'otc.trade_success';
            $balanceChangeService = resolve(BalanceChangeService::class);
            $balanceChangeService->to($otcTrade->buyer_id)
                ->withSymbol($otcTrade->coin)
                ->withNum($otcTrade->num)
                ->withModule($module)
                //->withInfo(resolve(CoinLogModuleService::class)->title($module))
                ->withInfo(new TranslateExpression('otc::message.撮合订单交易成功'))
                ->withNo($otcTrade->id)
                ->change();

            $otcTrade->setConfirmed()->saveIfFail();
            //改变挂单状态，挂单的数量为0,且所有订单都完成的时候，标记挂单状态已完成
            $otcExchangeService = resolve(OtcExchangeService::class);
            $otcExchangeService->checkExchangeNumOver($otcTrade->otcExchange, ['mark_success' => true]);
            //更新用户交易信息
            $otcUserService = resolve(OtcUserService::class);
            $otcUserService->updateTradeInfo($otcTrade->seller_id);
            $otcUserService->updateTradeInfo($otcTrade->buyer_id);

            return $otcTrade;
        });
        event(new TradeConfirmed($otcTrade));
        return $otcTrade;
    }


    /**
     * @param OtcTrade $otcTrade
     * @return bool|mixed
     * @throws OtcTradeException
     */
    public function timeoutCancel(OtcTrade $otcTrade)
    {
        if ($otcTrade->isWaitingPay()) {

            /** @var OtcExchange $otcExchange */
            $otcExchange = $otcTrade->otcExchange;
            if ($otcExchange->ensureExchangeRight()) {
                return \DB::transaction(function () use ($otcExchange, $otcTrade) {
                    $otcExchange->additionSurplus($otcTrade->num);
                    $otcExchange->setSelling()->save();
                    $otcTrade->setCancel()->save();
                    return true;
                });
            }
        }
        return false;
    }

}
