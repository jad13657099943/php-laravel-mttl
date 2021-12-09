<?php

namespace Modules\Mttl\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Modules\Mttl\Models\EnergyCardBuy;
use Modules\Mttl\Services\EnergyCardBuyService;
use Modules\Mttl\Services\RewardLogService;

class StaticRewardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo date('Y-m-d H:i:s') . "发放静态收益job：\n";
        $service = resolve(RewardLogService::class);

        EnergyCardBuy::query()
            ->where('surplus_days', '>', 0)
            ->where('begin_date', '<', date('Y-m-d H:i:s'))
//            ->where('finish_date', '>', date('Y-m-d H:i:s'))
//            ->orderByDesc('created_at')
            ->chunkById(100, function (Collection $buys) use ($service) {
                $buys->each(function (EnergyCardBuy $buy) use ($service) {
                    $service->staticReward($buy);
                });
            });
    }
}
