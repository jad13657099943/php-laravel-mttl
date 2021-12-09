<?php

namespace Modules\Coin\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Coin\Database\Seeders\admin\AdminMenuTableSeeder;
use Modules\Coin\Database\Seeders\admin\CoinLogModulesSeeder;

class CoinDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(CoinTableSeeder::class);

        $this->call(AdminMenuTableSeeder::class);

        $this->call(CoinLogModulesSeeder::class);

        $this->call(CoinConfigTableSeeder::class);
    }
}
