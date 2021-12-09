<?php

return [
    'name' => 'Coin',

    'tokenio' => [
        'host'   => env('TOKENIO_HOST'),
        'key'    => env('TOKENIO_KEY'),
        'secret' => env('TOKENIO_SECRET'),
    ],

    'exchange' => [
        'price_sync_exchange' => env('CCXT_DEFAUL_PRICE_SYNC', 'binanceus'),

        'exchanges' => [
            'bithumb' => [
                'class' => \ccxt\bithumb::class,
                'options' => []
            ],
            'gateio' => [
                'class'   => \ccxt\gateio::class,
                'options' => [],
            ],
            'binanceus'  => [
                'class'   => \ccxt\binanceus::class,
                'options' => [],
            ],
            'kraken' => [
                'class'   => \ccxt\kraken::class,
                'options' => [],
            ],
        ],
    ],
];
