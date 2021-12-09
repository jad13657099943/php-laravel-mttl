<?php

return [
    'name' => 'Coinv2',

    'tokenioV2' => [
        'host'   => env('TOKENIO_HOST_V2'),
        'key'    => env('TOKENIO_KEY_V2'),
        'secret' => env('TOKENIO_SECRET_V2'),
    ],

    'exchange' => [
        'price_sync_exchange' => env('CCXT_DEFAUL_PRICE_SYNC', 'binanceus'),

        'exchanges' => [
            'bithumb' => [
                'class' => \ccxt\bithumb::class,
                'options' => []
            ],
            'gateio' => [
                'class' => \ccxt\gateio::class,
                'options' => [],
            ],
            'binanceus' => [
                'class' => \ccxt\binanceus::class,
                'options' => [],
            ],
            'kraken' => [
                'class' => \ccxt\kraken::class,
                'options' => [],
            ],
        ],
    ],
];
