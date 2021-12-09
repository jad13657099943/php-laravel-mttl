<?php


namespace Modules\Otc\Database\Seeders;


use Illuminate\Database\Seeder;
use Modules\Core\Models\Admin\AdminMenu;

class AdminMenuTableSeeder extends Seeder
{
    public function run()
    {


        /* OTC */
        $otc = AdminMenu::create([
            'title' => 'OTC管理',
            'icon' => '',
            'url' => '',
            'status' => 1,
        ]);


        AdminMenu::create([
            'title' => 'OTC币种',
            'parent_id' => $otc->id,
            'icon' => '',
            'url' => route('m.otc.admin.otc.coin.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => 'OTC会员',
            'parent_id' => $otc->id,
            'icon' => '',
            'url' => route('m.otc.admin.otc.user.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '挂单列表',
            'parent_id' => $otc->id,
            'icon' => '',
            'url' => route('m.otc.admin.otc.exchange.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '撮合列表',
            'parent_id' => $otc->id,
            'icon' => '',
            'url' => route('m.otc.admin.otc.trade.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '申诉列表',
            'parent_id' => $otc->id,
            'icon' => '',
            'url' => route('m.otc.admin.otc.appeal.index', [], false),
            'status' => 1,
        ]);


    }
}
