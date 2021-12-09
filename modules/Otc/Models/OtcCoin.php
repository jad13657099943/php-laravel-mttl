<?php

namespace Modules\Otc\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\Otc\Models\Traits\Method\OtcCoinMethod;
use Modules\Otc\Models\Traits\Relationship\OtcCoinRelationship;

class OtcCoin extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    use OtcCoinRelationship,
        OtcCoinMethod;

    const BUY_ENABLE = 1;
    const BUY_DISABLE = 0;

    const SELL_ENABLE = 1;
    const SELL_DISABLE = 0;

    const ENABLE = 1;
    const DISABLE = 0;

    protected $fillable = [
        'id',
        'name',
        'coin',
        'buy',
        'sell',
        'min',
        'max',
        'price_cny',
        'is_enable'
    ];

    protected $hidden = [
        'deleted_at'
    ];
}
