<?php

namespace Modules\Mttl\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Modules\Coin\Jobs\ProcessTradeJob;
use Modules\Mttl\Services\MeitaUserService;
use Modules\User\Models\ProjectUser;

class AutomaticEvolutionJob implements ShouldQueue
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
        echo date('Y-m-d H:i:s') . "自动进化job：\n";
        $service = resolve(MeitaUserService::class);

        ProjectUser::query()
            ->where('type', 1)
            ->where('grade', '!=', 5)
            ->orderByDesc('user_id')
            ->chunkById(100, function (Collection $users) use ($service) {
                $users->each(function (ProjectUser $user) use ($service) {
                    $service->evolution($user);
                });
            });
    }
}
