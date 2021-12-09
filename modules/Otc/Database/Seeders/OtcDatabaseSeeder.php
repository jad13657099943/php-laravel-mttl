<?php

namespace Modules\Otc\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Coin\Models\CoinLogModules;

class OtcDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(OtcCoinTableSeeder::class);
        $this->call(CoinLogModulesTableSeeder::class);
        $this->call(AdminMenuTableSeeder::class);
        //$this->call("OthersTableSeeder");
    }
}
