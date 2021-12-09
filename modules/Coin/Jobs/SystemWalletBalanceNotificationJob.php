<?php


namespace Modules\Coin\Jobs;


use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Coin\Services\SystemWalletService;

/* 系统钱包余额不足提醒 */

class SystemWalletBalanceNotificationJob  implements ShouldQueue
{

    public function handle(){

        $service = resolve(SystemWalletService::class);
        $service->checkBalance();

    }

}
