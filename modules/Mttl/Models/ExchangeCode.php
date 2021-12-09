<?php

namespace Modules\Mttl\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeCode extends Model
{
    protected $table = 'mttl_exchange_code';

    protected $guarded = [];

    protected $casts = [
        'user_ids' => 'array'
    ];
}
