<?php

namespace Modules\Coinv2\Services;

use DB;
use Log;
use Exception;
use InvalidArgumentException;
use Carbon\Carbon;
use Modules\Coin\Models\CoinConfig;
use Modules\Coinv2\Components\TokenIO\TokenIO;
use Modules\Coinv2\Events\TokenioNoticeProcessed;
use Modules\Coinv2\Events\TokenioNoticeReceived;
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
     * @throws \Modules\Coinv2\Components\TokenIO\Exceptions\SignatureVerifyException
     */
    public function receiveViaRequest(Request $reqeust)
    {
        // 数据格式 {"nonce":"a1139b1594379104c5226059","type":"chain.from","data":{"tx_state":-1,"trade_no":"170"}}
        //$data = $this->tokenio->verifyViaRequest($reqeust);
        $rawBody = $reqeust->getBody();
        $data = json_decode($rawBody, true);
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
            'type' => $data['type'],
            'nonce' => $data['nonce'],
            'version' => 2,
        ]);

        $notice->fill([
            'data' => $data['data'],
            'state' => CoinTokenioNotice::STATE_PROCESSING,
        ]);

        $notice->saveIfFail();

        //event(new TokenioNoticeReceived($notice));

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
        if ($notice->state == 0 || ($options['force'] ?? true)) {
            try {
                if ($notice->type == 'recharge') {
                    $this->processRecharge($notice, $options['rechargeOptions'] ?? []);
                } elseif ($notice->type == 'withdraw') {
                    $this->processWithdraw($notice, $options['withdrawOptions'] ?? []);
                } else {
                    throw new InvalidArgumentException(trans('不支持的通知类型: :type, id: :id', [
                        'type' => $notice->type,
                        'id' => $notice->id,
                    ]));
                }
            } catch (Exception $e) {
                Log::alert(trans('TokenIO回调处理异常'), ['exception' => $e, 'notice' => $notice]);

                // 直接更新为-1，不再处理
                $notice->setDontProcess()
                    ->saveIfFail();
            }

            /*event(new TokenioNoticeProcessed($notice));*/
        }
    }

    /**
     * 处理充值通知
     *
     * @param CoinTokenioNotice $notice
     * @param array $options
     *
     * @throws ModelSaveException
     * @throws \Modules\Coinv2\Exceptions\CoinNotFoundException
     * @throws \Throwable
     */
    protected function processRecharge(CoinTokenioNotice $notice, array $options = [])
    {

        $tradeId = $notice->data['id'] ?? 0;
        $chain = $notice->data['chain'] ?? '';
        //获取tokenio返回的充值信息
        $result = $this->tokenio->rechargeInfo($tradeId, null, $chain);
        if ($result['code'] != 0) {
            //查询失败，不做处理
            $notice->state = -5;
            $notice->save();
            return $notice;
        }
        $result = $result['data'] ?? [];

        // 查找CoinConfig
        $symbol = CoinConfig::query()
            ->where('chain', $chain)
            ->where('agreement', $result['symbol'])
            ->value('symbol');
        if (!$symbol) {
            // 未找到币种，不做处理
            $notice->state = -1;
            $notice->save();
            return $notice;
        }

        /** @var CoinService $coinService */
        $coinService = resolve(CoinService::class);
        $coin = $coinService->getBySymbol($symbol);

        // 没启用的币种不做处理、币种未开启充值、充值数据小于最小充值额
        if (!$coin->canRechargeWith($result['value'])) {
            return false;
        }

        if ($result['value'] <= 0) {
            $notice->state = -1;
            $notice->save();
            return $notice;
        }

        DB::transaction(function () use ($notice, $result, $coin, $chain) {
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
                    'to' => $result['to'],
                    'symbol' => $coin->symbol,
                    'chain' => $chain,
                    'from' => $result['from'],
                    'value' => $result['value'],
                    'memo' => $result['memo'] ?? '',
                    'hash' => $result['hash'],
                    'state' => CoinRecharge::STATE_FINISHED,
                    'block_number' => $result['blockNumber'],
                    'packaged_at' => Carbon::createFromTimestamp(strtotime($result['timestamp'])),
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

    /**
     * 处理提现
     *
     * @param CoinTokenioNotice $notice
     * @param array $options
     */
    protected function processWithdraw(CoinTokenioNotice $notice, array $options = [])
    {

        $tradeNo = $notice->data['trade_no'] ?? 0;
        $chain = $notice->data['chain'] ?? '';
        // 获取tokenio返回的提现信息
        $result = $this->tokenio->withdrawInfo($tradeNo, $chain);
        if ($result['code'] != 0) {
            $notice->state = -5;
            $notice->save();
            return $notice;
        }

        $state = $result['data']['state'] ?? 0;
        if ($state != 2) {
            //没成功的通知，直接不处理
            $notice->state = -1;
            $notice->save();
            return $notice;
        }

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
            $withdraw->state = $result['data']['state'];
            $withdraw->saveOrFail();
        }

        $trade->hash = $result['data']['hash'] ?? null;
        $trade->state = CoinTrade::STATE_FINISHED;
        $trade->saveIfFail();

        $notice->setProcessed()
            ->saveIfFail();

    }
}
