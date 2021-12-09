<?php

namespace Modules\Otc\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Coin\Models\CoinLogModules;
use Modules\Coin\Services\CoinLogModuleService;

class CoinLogModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $coinLogModuleService = resolve(CoinLogModuleService::class);
        /*$coinLogModuleService->addModule('otc', '场外交易');
        $coinLogModuleService->addActions('otc', [
            ['action' => 'exchange_buy', 'title' => '挂单买入冻结资金', 'remark' => ''],
            ['action' => 'exchange_sell', 'title' => '挂单卖出冻结资金', 'remark' => ''],
            ['action' => 'trade_buy_frozen', 'title' => '撮合买入冻结资金', 'remark' => ''],
            ['action' => 'trade_sell', 'title' => '撮合订单卖出扣除资金', 'remark' => ''],
            ['action' => 'trade_buy', 'title' => '撮合订单买入增加资金', 'remark' => ''],
            ['action' => 'trade_success', 'title' => '撮合订单交易成功', 'remark' => ''],
            ['action' => 'exchange_rollback', 'title' => '挂单订单取消，返还资金', 'remark' => '']
        ]);*/
        $coinLogModuleService->addModule('otc', '场外交易');
        $coinLogModuleService->addActions('otc', [
            ['action' => 'exchange_buy', 'title' => ['zh_CN' => '挂单买入冻结资金', 'en_US' => 'Pending order to buy frozen funds'], 'remark' => ''],
            ['action' => 'exchange_sell', 'title' => ['zh_CN' => '挂单卖出冻结资金', 'en_US' => 'Pending orders sell frozen funds'], 'remark' => ''],
            ['action' => 'trade_buy_frozen', 'title' => ['zh_CN' => '撮合买入冻结资金', 'en_US' => 'Matching to buy frozen funds'], 'remark' => ''],
            ['action' => 'trade_sell', 'title' => ['zh_CN' => '撮合订单卖出扣除资金', 'en_US' => 'Matching orders and selling deductions'], 'remark' => ''],
            ['action' => 'trade_buy', 'title' => ['zh_CN' => '撮合订单买入增加资金', 'en_US' => 'Matching order purchases to increase funds'], 'remark' => ''],
            ['action' => 'trade_success', 'title' => ['zh_CN' => '撮合订单交易成功', 'en_US' => 'Successfully matched orders'], 'remark' => ''],
            ['action' => 'exchange_rollback', 'title' => ['zh_CN' => '挂单订单取消，返还资金', 'en_US' => 'Cancellation of pending orders, return of funds'], 'remark' => '']
        ]);
    }
}
