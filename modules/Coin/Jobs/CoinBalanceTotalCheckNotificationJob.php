<?php


namespace Modules\Coin\Jobs;


use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Coin\Services\AssetService;

/*
 * 系统合计余额异动提现
    定时统计(如每格1个小时)，当前合计数-上次合计数>设置数量。则提醒
*/

class CoinBalanceTotalCheckNotificationJob implements ShouldQueue
{

    public function handle(){

        $service = resolve(AssetService::class);
        $service->balanceTotalCheck();

    }

}
