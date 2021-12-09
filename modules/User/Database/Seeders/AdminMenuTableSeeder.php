<?php


namespace Modules\User\Database\Seeders;


use Illuminate\Database\Seeder;
use Modules\Core\Models\Admin\AdminMenu;

class AdminMenuTableSeeder extends Seeder
{
    public function run()
    {

        //提前是先运行core模块的数据填充
        AdminMenu::create([
            'title' => '会员列表',
            'parent_id' => 1,
            'icon' => '',
            'url' => route('m.user.admin.user.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '推荐树',
            'parent_id' => 1,
            'icon' => '',
            'url' => route('m.user.admin.user.tree', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '会员工单',
            'parent_id' => 1,
            'icon' => '',
            'url' => route('m.user.admin.appeal.index', [], false),
            'status' => 1,
        ]);

        AdminMenu::create([
            'title' => '项目设置',
            'parent_id' => 10,
            'icon' => '',
            'url' => route('m.user.admin.setting.index', [], false),
            'status' => 1,
        ]);
    }
}
