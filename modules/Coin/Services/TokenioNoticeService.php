<?php

namespace Modules\Coin\Services;

use DB;
use Log;
use Exception;
use InvalidArgumentException;
use Carbon\Carbon;
use Modules\Coin\Components\TokenIO\TokenIO;
use Modules\Coin\Events\TokenioNoticeProcessed;
use Modules\Coin\Events\TokenioNoticeReceived;
use Modules\Coin\Models\CoinChainTrade;
use Modules\Coin\Models\CoinRecharge;
use Modules\Coin\Models\CoinTokenioNotice;
use Modules\Coin\Models\CoinTrade;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Exceptions\UnexpectedDataException;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;
use Psr\Http\Message\ServerRequestInterface as Request;

class TokenioNoticeService
{
    use HasQuery;

    /**
     * @var CoinTokenioNotice
     */
    protected $model;
    /**
     * @var TokenIO
     */
    protected $tokenio;

    public function __construct(CoinTokenioNotice $model, TokenIO $tokenio)
    {
        $this->model = $model;
        $this->tokenio = $tokenio;
    }

    /**
     * @return TokenIO
     */
    public function tokenio(): TokenIO
    {
        return $this->tokenio;
    }

    /**
     * @param Request $reqeust
     *
     * @return CoinTokenioNotice
     * @throws \Modules\Coin\Components\TokenIO\Exceptions\SignatureVerifyException
     */
    public function receiveViaRequest(Request $reqeust)
    {
        // 数据格式 {"nonce":"a1139b1594379104c5226059","type":"chain.from","data":{"tx_state":-1,"trade_no":"170"}}
        $data = $this->tokenio->verifyViaRequest($reqeust);

        return $this->receive($data);
    }

    /**
     * @param array $data
     *
     * @return CoinTokenioNotice
     */
    public function receive(array $data)
    {
        /** @var CoinTokenioNotice $notice */
        $notice = $this->query()->firstOrNew([
            'type'  => $data['type'],
            'nonce' => $data['nonce'],
            'version' => 1,
        ]);

        $notice->fill([
            'data'  => $data['data'],
            'state' => CoinTokenioNotice::STATE_PROCESSING,
        ]);

        $notice->saveIfFail();

        event(new TokenioNoticeReceived($notice));

        return $notice;
    }

    /**
     * 处理接收的TokenIO通知
     *
     * @param CoinTokenioNotice $notice
     * @param TokenIO $tokenio
     * @param array $options
     *
     * @throws \Throwable
     */
    public function process(CoinTokenioNotice $notice, array $options = [])
    {
        if ($notice->needProcess() || ($options['force'] ?? true)) {
            try {
                if ($notice->isRecharge()) {
                    $this->processRecharge($notice, $options['rechargeOptions'] ?? []);
                } elseif ($notice->isWithdraw()) {
                    $this->processWithdraw($notice, $options['withdrawOptions'] ?? []);
                } else {
                    throw new InvalidArgumentException(trans('不支持的通知类型: :type, id: :id', [
                        'type' => $notice->type,
                        'id'   => $notice->id,
                    ]));
                }
            } catch (Exception $e) {
                Log::alert(trans('TokenIO回调处理异常'), ['exception' => $e, 'notice' => $notice]);

                // 直接更新为-1，不再处理
                $notice->setDontProcess()
                       ->saveIfFail();
            }

            event(new TokenioNoticeProcessed($notice));
        }
    }

    /**
     * 处理充值通知
     *
     * @param CoinTokenioNotice $notice
     * @param array $options
     *
     * @throws ModelSaveException
     * @throws \Modules\Coin\Exceptions\CoinNotFoundException
     * @throws \Throwable
     */
    protected function processRecharge(CoinTokenioNotice $notice, array $options = [])
    {
        if ($notice->isDataSucceeded()) { // 判断链上充值已成功
            $tradeId = $notice->data['chain_trade_id'] ?? 0;
            //获取tokenio返回的充值信息
            $result = $this->tokenio->rechargeInfo($tradeId); // 调用充值通知处理逻辑

            /** @var CoinService $coinService */
            $coinService = resolve(CoinService::class);
            $coin = $coinService->getBySymbol($result['symbol']);
            $chain = $result['chain'];

            // 没启用的币种不做处理、币种未开启充值、充值数据小于最小充值额
            if ( ! $coin->canRechargeWith($result['value'])) {
                return;
            }

            //判断本地表coin_trade是否有相应的充值补gas记录，
            //有的话，可能是这个补gas的记录产生的补gas充值记录引起的充值入账
            //如果是补gas充值记录引起的，则不要入账到余额中(没有一个确定的参数来确定该条记录)
            $gasLog = CoinTrade::query()->where('from', $result['from'])
                ->where('to', $result['to'])
                ->where('symbol', $result['symbol'])
                ->where('action', 'gas')
                ->where('num', floatval($result['value']))
                ->first();
            if ($gasLog) {
                //直接标记为已处理
                $notice->setProcessed()->saveIfFail();
                return false;
            }

            DB::transaction(function () use ($notice, $result, $coin,$chain) {
                /** @var RechargeService $rechargeService */
                $rechargeService = resolve(RechargeService::class);
                /** @var CoinRecharge $recharge */
                $recharge = $rechargeService->getByHash($result['hash'], array_merge([
                    'exception' => false
                ], $options['oneOptions'] ?? []));

                if ($recharge) { // 有记录提示记录操作
                    $recharge->state = CoinRecharge::STATE_FINISHED;
                    $recharge->saveIfFail();
                } else { // 插入新纪录
                    $rechargeService->createWithToWallet([
                        'to'           => $result['to'],
                        'symbol'       => $coin->symbol,
                        'chain'        => $chain,
                        'from'         => $result['from'],
                        'value'        => $result['value'],
                        'memo'         => $result['memo'],
                        'hash'         => $result['hash'],
                        'state'        => CoinRecharge::STATE_FINISHED,
                        'block_number' => $result['block_number'],
                        'packaged_at'  => Carbon::createFromTimestamp(strtotime($result['timestamp'])),
                    ], array_merge([
                        'changeBalanceOptions' => [
                            'info' => new TranslateExpression("coin::message.链上充值"),
                        ],
                    ], $options['createOptions'] ?? []));
                }

                $notice->setProcessed()
                       ->saveIfFail();
            });
        }
    }

    /**
     * 处理提现
     *
     * @param CoinTokenioNotice $notice
     * @param array $options
     */
    protected function processWithdraw(CoinTokenioNotice $notice, array $options = [])
    {
        if ($notice->isDataSucceeded()) { // 判断链上交易已成功
            $tradeNo = $notice->data['trade_no'] ?? 0;
            // 获取tokenio返回的提现信息
            $result = $this->tokenio->withdrawInfo($tradeNo);

            /** @var TradeService $tradeService */
            $tradeService = resolve(TradeService::class);
            /** @var CoinTrade $trade */
            $trade = $tradeService->getById($tradeNo);

            if ($trade->isWithdraw()) { // 提现回调
                /** @var WithdrawService $withdrawService */
                $withdrawService = resolve(WithdrawService::class);
                $withdraw = $withdrawService->getById($trade->no);

                // 根据trade_no的id值就是trade_log.id,在查询trade_log.module_no+trade_log.module=withdraw，
                // 查询出 coin_withdraw表的id，更新coin_withdraw的state状态
                $withdraw->state = $result['state'];
                $withdraw->saveOrFail();
            } // gas和归集 不需要处理 直接设定成功

            $trade->state = CoinTrade::STATE_FINISHED;
            $trade->saveIfFail();

            $notice->setProcessed()
                   ->saveIfFail();
        }
    }
}
