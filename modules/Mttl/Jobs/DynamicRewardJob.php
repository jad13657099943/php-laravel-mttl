<?php

namespace Modules\Mttl\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Mttl\Models\EnergyCardBuy;
use Modules\Mttl\Services\RewardLogService;

class DynamicRewardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cardBuy;

    /**
     * Create a new job instance.
     *
     * @param EnergyCardBuy $cardBuy
     * @return void
     */
    public function __construct(EnergyCardBuy $cardBuy)
    {
        $this->cardBuy = $cardBuy->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = resolve(RewardLogService::class);
        $service->dynamicReward($this->cardBuy);
    }
}
