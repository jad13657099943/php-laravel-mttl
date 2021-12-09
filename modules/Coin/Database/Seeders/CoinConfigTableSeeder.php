<?php


namespace Modules\Coin\Database\Seeders;


use Illuminate\Database\Seeder;
use Modules\Coin\Models\CoinConfig;

class CoinConfigTableSeeder extends Seeder
{
    public function run()
    {
        CoinConfig::create([
            'symbol' => 'BTC',
            'chain' => 'BTC',
            'agreement' => 'BTC',
            'tokenio_version' => 1,
            'recharge_state' => 1,
            'withdraw_state' => 1
        ]);

        CoinConfig::create([
            'symbol' => 'ETH',
            'chain' => 'ETH',
            'agreement' => 'ETH',
            'tokenio_version' => 2,
            'recharge_state' => 1,
            'withdraw_state' => 1
        ]);

        CoinConfig::create([
            'symbol' => 'FIL',
            'chain' => 'FIL',
            'agreement' => 'FIL',
            'tokenio_version' => 2,
            'recharge_state' => 1,
            'withdraw_state' => 1
        ]);

        CoinConfig::create([
            'symbol' => 'USDT',
            'chain' => 'ETH',
            'agreement' => 'ERC20',
            'tokenio_version' => 1,
            'recharge_state' => 1,
            'withdraw_state' => 1
        ]);

        CoinConfig::create([
            'symbol' => 'USDT',
            'chain' => 'TRX',
            'agreement' => 'TRC20',
            'tokenio_version' => 2,
            'recharge_state' => 1,
            'withdraw_state' => 1
        ]);
    }
}
