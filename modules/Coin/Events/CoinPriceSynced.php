<?php

namespace Modules\Coin\Events;

use Illuminate\Support\Collection;

class CoinPriceSynced
{
    /**
     * @var array|Collection
     */
    public $price;

    public function __construct($price)
    {
        $this->price = $price;
    }
}
