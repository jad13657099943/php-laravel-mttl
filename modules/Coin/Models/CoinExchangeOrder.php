<?php

namespace Modules\Coin\Models;

use Illuminate\Database\Eloquent\Model;

class CoinExchangeOrder extends Model
{
    const STATE_FAIL = -1;
    const STATE_NOT = 0;
    const STATE_SUCCESS = 1;

    public $table = 'exchange_order';
    public $guarded = [];
}
