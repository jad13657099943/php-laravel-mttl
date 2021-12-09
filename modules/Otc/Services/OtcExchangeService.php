<?php


namespace Modules\Otc\Services;

use Modules\Coin\Services\BalanceChangeService;
use Modules\Coin\Services\CoinLogModuleService;
use Modules\Core\Exceptions\Frontend\Auth\UserAuthVerifyException;
use Modules\Core\Exceptions\Frontend\Auth\UserPayPasswordEmptyException;
use Modules\Core\Exceptions\Frontend\Auth\UserPayPasswordException;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Services\Frontend\UserBankService;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Models\Frontend\UserBank;
use Modules\Core\Translate\TranslateExpression;
use Modules\Otc\Events\ExchangeCanceled;
use Modules\Otc\Events\ExchangeCreated;
use Modules\Otc\Events\ExchangeSucceed;
use Modules\Otc\Exceptions\OtcExchangeException;
use Modules\Otc\Models\OtcCoin;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcUser;

class OtcExchangeService
{
    use HasQuery {
        create as queryCreate;
    }

    protected $type = '';
    /**
     * @var OtcExchange
     */
    protected $model;

    public function __construct(OtcExchange $model)
    {
        $this->model = $model;
    }

    /**
     * @param $user
     * @param array $data
     * @param array $options
     * @return mixed
     * @throws OtcExchangeException
     * @throws UserAuthVerifyException
     * @throws UserPayPasswordEmptyException
     * @throws UserPayPasswordException
     * @throws ModelSaveException
     */
    public function create($user, array $data, array $options = [])
    {
        $user = with_user($user);
        /** @var OtcCoin $otcCoin */
        $otcCoin = resolve(OtcCoinService::class)->one(['coin' => strtolower($data['coin'])]);

        //检查币种交易金额是否处于合法范围
//        $otcCoin->checkNumRange($data['num']);
        //单笔交易量是否处于合法范围
        $otcCoin->checkNumRange($data['min']);
        $otcCoin->checkNumRange($data['max']);


        /** @var $otcUser OtcUser */
        $otcUser = resolve(OtcUserService::class)->firstOrCreate(['user_id' => $user->id]);
        //校验实名认证
        $user->isAuthVerified();
        //校验交易密码
        $user->isPayPasswordSet();
        $user->checkPayPassword($data['pay_password'], true);

        if ($data['type'] == OtcExchange::TYPE_BUY) {
            $bankService = resolve(UserBankService::class);
            $enableBankList = $bankService->enableBankList($user, $data['bank_list'])->keyBy('bank');
            if (count($data['bank_list']) != count($enableBankList)) {
                throw new OtcExchangeException(trans('otc::exception.付款支付方式有误'));
            }
        }

        $exchange = \DB::transaction(function () use ($otcUser, $otcCoin, $data, $options) {

            /** @var $exchange OtcExchange */
            $exchange = $this->queryCreate(array_merge($data, [
                'user_id' => $otcUser->user_id,
                'surplus' => $data['num'],
                'status' => OtcExchange::STATUS_NORMAL,
                'no' => generate_otc_no(OtcExchange::class)
            ]), $options);

            $exchange->setRelation('otcUser', $otcUser);
            $exchange->setRelation('otcCoin', $otcCoin);
            $exchange->canExchange();

            if ($exchange->isSell()) {
                $module = 'otc.exchange_sell';
                //卖单先扣除用户钱包对应的金额
                $balanceChangeService = resolve(BalanceChangeService::class);
                $res = $balanceChangeService->from($otcUser->user_id)
                    ->withSymbol($data['coin'])
                    ->withNum($data['num'])
                    ->withModule($module)
                    //->withInfo(resolve(CoinLogModuleService::class)->title($module))
                    ->withInfo(new TranslateExpression('otc::message.挂单卖出冻结资金'))
                    ->withNo($exchange->id)
                    ->change();
            }

            return $exchange;
        });

        event(new ExchangeCreated($exchange));
        return $exchange;
    }


    /**
     * 下架,取消
     */
    public function cancel($user, $otcExchange, $options = [])
    {
        $user = with_user($user);
        $otcExchange = with_otc_exchange($otcExchange);

        if ($otcExchange->user_id != $user->id) {
            throw new OtcExchangeException(trans('otc::exception.无法取消该挂单交易'));
        }

        //校验状态
        $otcExchange->canExchanging(true);

        if ($otcExchange->surplus > 0) {
            $otcExchange = \DB::transaction(function () use ($otcExchange) {
                if ($otcExchange->isSell()) {
                    $this->rollback($otcExchange);
                }
                $otcExchange->setCanceled()->saveOrFail();

                return $otcExchange;
            });
            event(new ExchangeCanceled($otcExchange));
            return $otcExchange;
        }
        throw new OtcExchangeException(trans("otc::exception.剩余可交易数量为0，无需取消"));
    }


    /**
     * 回滚剩余可以交易的
     *
     * @param OtcExchange $otcExchange
     * @param $num
     * @return array|null[]
     * @throws ModelSaveException
     * @throws \Throwable
     */
    public function rollback($otcExchange, $num = 0)
    {
        $otcExchange = with_otc_exchange($otcExchange);

        if ($otcExchange->surplus < $num) {
            throw new OtcExchangeException(trans('otc::exception.回滚数量不得高于剩余待交易数量'));
        }
        $num = $num === 0 ? $otcExchange->surplus : $num;

        if ($otcExchange->ensureExchangeRight()) {
            $balanceChangeService = resolve(BalanceChangeService::class);
            $module = 'otc.exchange_rollback';
            return $balanceChangeService->to($otcExchange->user_id)
                ->withSymbol($otcExchange->coin)
                ->withNum($num)
                ->withModule($module)
                //->withInfo(resolve(CoinLogModuleService::class)->title($module))
                ->withInfo(new TranslateExpression('coin::message.挂单订单取消，返还资金'))
                ->withNo($otcExchange->id)
                ->change();
        } else {
            throw new OtcExchangeException(trans("otc::exception.挂单交易总额异常"));
        }
    }


    /**
     * 检查挂单交易数量是否已经全部交易，并且撮合订单也全部交易成功
     *
     * @param OtcExchange $otcExchange
     * @param array $options
     * @return bool
     */
    public function checkExchangeNumOver(OtcExchange $otcExchange, $options = [])
    {
        $isOver = $otcExchange->isNumOver();

        if ($isOver) {
            $markSuccess = $options['mark_success'] ?? false;
            if ($markSuccess) {
                //如果全部撮合订单都交易完成，标记挂单状态为已完成
                $otcExchange->setSucceed()->save();
                event(new ExchangeSucceed($otcExchange));
                return true;
            }
        }
        return $isOver;

    }

}
