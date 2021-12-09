<?php

return [
    'name' => 'Web3',

    'eth' => [
        'host' => env('ETH_JSON_PRC', '')
    ],

    'tron' => [
        'host' => env('TRON_JSON_PRC', 'https://api.trongrid.io')
    ]
];
