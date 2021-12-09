<?php


namespace Modules\Coin\Database\Seeders\admin;


use Illuminate\Database\Seeder;
use Modules\Core\Models\Admin\AdminMenu;

class AdminMenuTableSeeder extends Seeder
{

    public function run()
    {


        /* 资产管理 */
        $asset = AdminMenu::create([
            'title' => '资产管理',
            'icon' => '',
            'url' => '',
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '币种管理',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.coin.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '系统钱包',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.system_wallet.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '会员钱包',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.user_wallet.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '提现列表',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.withdraw.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '充值列表',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.recharge.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '内部转账',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.internal.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '链上转账',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.coin_trade.index', [], false),
            'status' => 1,
        ]);


        $total = AdminMenu::create([
            'title' => '充提统计',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => '',
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '充币统计',
            'parent_id' => $total->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.recharge.total', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '提币统计',
            'parent_id' => $total->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.withdraw.total', [], false),
            'status' => 1,
        ]);


        $userAsset = AdminMenu::create([
            'title' => '用户资产',
            'parent_id' => $asset->id,
            'icon' => '',
            'url' => '',
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '资产余额',
            'parent_id' => $userAsset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.coin_asset.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '变更记录',
            'parent_id' => $userAsset->id,
            'icon' => '',
            'url' => route('m.coin.admin.asset.coin_asset.coin_log', [], false),
            'status' => 1,
        ]);

    }

}
