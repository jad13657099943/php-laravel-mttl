<?php

namespace Modules\Coin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Coin\Services\CoinService;

class SyncCoinPriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    public $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var CoinService $coinService */
        $coinService = resolve(CoinService::class);
        $coinService->cacheSyncPrice(array_merge([
            "force" => true
        ], $this->options));
    }
}
