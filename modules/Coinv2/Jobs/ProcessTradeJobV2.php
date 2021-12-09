<?php

namespace Modules\Coinv2\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Coin\Models\CoinTrade;
use Modules\Coinv2\Services\TradeService;

/**
 * 处理链上交易任务
 *
 * @package Modules\Coinv2\Jobs
 */
class ProcessTradeJobV2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var CoinTrade
     */
    public $trade;

    /**
     * @var array $options
     */
    public $options;

    public function __construct(CoinTrade $trade, array $options = [])
    {
        $this->trade = $trade;
        $this->options = $options;
    }

    public function handle()
    {
        /** @var TradeService $tradeService */
        $tradeService = resolve(TradeService::class);
        $tradeService->process($this->trade, $this->options);
    }
}
