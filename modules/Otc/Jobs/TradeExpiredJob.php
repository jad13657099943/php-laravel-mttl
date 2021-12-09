<?php

namespace Modules\Otc\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Otc\Exceptions\OtcExchangeException;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Services\OtcTradeService;
use Modules\Otc\Models\OtcTrade;

class TradeExpiredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * @var OtcTrade
     */
    public $otcTrade;

    public $otcTradeService;

    /**
     * TradeExpiredJob constructor.
     * @param OtcTrade $otcTrade
     */
    public function __construct(OtcTrade $otcTrade)
    {
        $this->otcTrade = $otcTrade;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Modules\Otc\Exceptions\OtcTradeException
     */
    public function handle()
    {
        $otcTradeService = resolve(OtcTradeService::class);
        $otcTradeService->timeoutCancel($this->otcTrade);
    }
}
