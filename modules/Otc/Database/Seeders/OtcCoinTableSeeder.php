<?php

namespace Modules\Otc\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Otc\Models\OtcCoin;

class OtcCoinTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $otcCoinList = [
            [
                'name' => 'USDT',
                'coin' => 'USDT',
                'buy' => OtcCoin::BUY_ENABLE,
                'sell' => OtcCoin::SELL_ENABLE,
                'min' => 100,
                'max' => 10000,
                'price_cny' => 0,
                'is_enable' => OtcCoin::ENABLE
            ],
            [
                'name' => 'BTC',
                'coin' => 'BTC',
                'buy' => OtcCoin::BUY_ENABLE,
                'sell' => OtcCoin::SELL_ENABLE,
                'min' => 0.01,
                'max' => 2,
                'price_cny' => 0,
                'is_enable' => OtcCoin::ENABLE
            ],
            [
                'name' => 'FUN',
                'coin' => 'FUN',
                'buy' => OtcCoin::BUY_ENABLE,
                'sell' => OtcCoin::SELL_ENABLE,
                'min' => 0.01,
                'max' => 100,
                'price_cny' => 0,
                'is_enable' => OtcCoin::ENABLE
            ],
            [
                'name' => 'CNY',
                'coin' => 'CNY',
                'buy' => OtcCoin::BUY_DISABLE,
                'sell' => OtcCoin::BUY_DISABLE,
                'min' => 100,
                'max' => 10000,
                'price_cny' => 0,
                'is_enable' => OtcCoin::DISABLE
            ],
        ];
        \DB::table('otc_coins')->insert($otcCoinList);
    }
}
