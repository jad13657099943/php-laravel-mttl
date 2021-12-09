<?php


namespace Modules\Coin\Database\Seeders\admin;

use Illuminate\Database\Seeder;
use Modules\Coin\Models\CoinLogModules;
use Modules\Coin\Services\CoinLogModuleService;

class CoinLogModulesSeeder extends Seeder
{

    public function run()
    {


        $coinLogModuleService = resolve(CoinLogModuleService::class);
        /*$coinLogModuleService->addModule('coin', '钱包');
        $coinLogModuleService->addActions('coin', [
            ['action' => 'withdraw_to', 'title' => '提现', 'remark' => ''],
            ['action' => 'withdraw_return', 'title' => '撤销提现', 'remark' => ''],
            ['action' => 'recharge_from', 'title' => '充值', 'remark' => ''],
            ['action' => 'pooling_cold', 'title' => '归集', 'remark' => ''],
            ['action' => 'gas', 'title' => '补GAS', 'remark' => ''],
            ['action' => 'recharge_manual', 'title' => '手动', 'remark' => '']
        ]);*/

        $coinLogModuleService->addModule('coin', '钱包');
        $coinLogModuleService->addActions('coin', [
            ['action' => 'withdraw_to', 'title' => ['zh_CN' => '提现', 'en_US' => 'withdraw'], 'remark' => ''],
            ['action' => 'withdraw_return', 'title' => ['zh_CN'=>'撤销提现','en_US'=>'Withdraw withdrawal'], 'remark' => ''],
            ['action' => 'recharge_from', 'title' => ['zh_CN'=>'充值','en_US'=>'recharge'], 'remark' => ''],
            ['action' => 'pooling_cold', 'title' => ['zh_CN'=>'归集','en_US'=>'pooling_cold'], 'remark' => ''],
            ['action' => 'gas', 'title' => ['zh_CN'=>'补GAS','en_US'=>'gas'], 'remark' => ''],
            ['action' => 'recharge_manual', 'title' => ['zh_CN'=>'手动','en_US'=>'manual'], 'remark' => ''],
            ['action' => 'exchange_inc', 'title' => ['zh_CN'=>'兑换增加','en_US'=>'Exchange increase'], 'remark' => ''],
            ['action' => 'exchange_dec', 'title' => ['zh_CN'=>'兑换减少','en_US'=>'Exchange decrease'], 'remark' => ''],
            ['action' => 'admin_change', 'title' => ['zh_CN'=>'后台操作','en_US'=>'Admin change'], 'remark' => ''],
        ]);
    }

}
