<?php

namespace Modules\Coin\Database\Seeders;


use Illuminate\Database\Seeder;
use Modules\Coin\Models\Coin;

class CoinTableSeeder extends Seeder
{

    public function run()
    {
        Coin::create([
            'chain' => 'CNY',
            'symbol' => 'CNY',
            'coin' => 'CNY',
            'short_name' => 'CNY',
            'full_name' => '人民币',
            'real_symbol' => 'CNY',
            'unique' => '',
            'decimals' => 8,
            'hot_max' => 0,
            'withdraw_min' => 100,
            'withdraw_state' => Coin::WITHDRAW_STATE_CLOSE,
            'withdraw_fee' => 0,
            'recharge_min' => 0.0001,
            'recharge_state' => Coin::RECHARGE_STATE_CLOSE,
            'state_updated_at' => now(),
            'status' => Coin::STATUS_ENABLED,
            'cold_min' => 10,
            'comment' => '',
            'contract_hash' => '',
            'unit_decimals' => 8,
            'balance_decimals' => 4,
            'internal_state' => Coin::STATUS_DISABLED,
            'gas_price' => 0,
            'icon' => '',
        ]);

        Coin::create([
            'chain' => 'BTC',
            'symbol' => 'BTC',
            'coin' => 'BTC',
            'short_name' => 'BTC',
            'full_name' => '比特币',
            'real_symbol' => 'BTC',
            'unique' => '',
            'decimals' => 8,
            'hot_max' => 0,
            'withdraw_min' => 100,
            'withdraw_state' => Coin::WITHDRAW_STATE_OPEN,
            'withdraw_fee' => 0,
            'recharge_min' => 0.0001,
            'recharge_state' => Coin::RECHARGE_STATE_OPEN,
            'state_updated_at' => now(),
            'status' => Coin::STATUS_ENABLED,
            'cold_min' => 0.001,
            'comment' => '',
            'contract_hash' => '',
            'unit_decimals' => 8,
            'balance_decimals' => 4,
            'internal_state' => Coin::INTERNAL_STATE_OPEN,
            'gas_price' => 0,
            'icon' => '',
        ]);

        Coin::create([
            'chain' => 'ETH',
            'symbol' => 'ETH',
            'coin' => 'ETH',
            'short_name' => 'ETH',
            'full_name' => '以太坊',
            'real_symbol' => 'ETH',
            'unique' => '',
            'decimals' => 18,
            'hot_max' => 0,
            'withdraw_min' => 100,
            'withdraw_state' => Coin::WITHDRAW_STATE_OPEN,
            'withdraw_fee' => 0,
            'recharge_min' => 0.05,
            'recharge_state' => Coin::RECHARGE_STATE_OPEN,
            'state_updated_at' => now(),
            'status' => Coin::STATUS_ENABLED,
            'cold_min' => 0.01,
            'comment' => '',
            'contract_hash' => '',
            'unit_decimals' => 8,
            'balance_decimals' => 4,
            'internal_state' => Coin::INTERNAL_STATE_OPEN,
            'gas_price' => 0,
            'icon' => '',
        ]);

        Coin::create([
            'chain' => 'ETH',
            'symbol' => 'USDT',
            'coin' => 'USDT',
            'short_name' => 'USDT',
            'full_name' => '泰达币',
            'real_symbol' => 'USDT',
            'unique' => '',
            'decimals' => 18,
            'hot_max' => 0,
            'withdraw_min' => 100,
            'withdraw_state' => Coin::WITHDRAW_STATE_OPEN,
            'withdraw_fee' => 0,
            'recharge_min' => 0.0001,
            'recharge_state' => Coin::RECHARGE_STATE_OPEN,
            'state_updated_at' => now(),
            'status' => Coin::STATUS_ENABLED,
            'cold_min' => 1,
            'comment' => '',
            'contract_hash' => '',
            'unit_decimals' => 8,
            'balance_decimals' => 4,
            'internal_state' => Coin::INTERNAL_STATE_OPEN,
            'gas_price' => 0,
            'icon' => '',
        ]);
    }
}
