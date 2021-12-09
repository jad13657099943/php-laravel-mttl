<?php

namespace Modules\Otc\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\Otc\Models\Traits\Method\OtcUserMethod;
use Modules\Otc\Models\Traits\Relationship\OtcUserRelationship;

class OtcUser extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    use OtcUserRelationship,
        OtcUserMethod;

    const BUY_ENABLE = 1;
    const BUY_DISABLE = 0;

    const SELL_ENABLE = 1;
    const SELL_DISABLE = 0;


    protected $fillable = [
        'id',
        'user_id',
        'success_buy',
        'success_sell',
        'average_time',
        'enable_buy',
        'enable_sell',
        'buy_rate',
        'sell_rate',
        'buy_cost',
        'sell_cost',
        'sell_min',
        'sell_max',
        'last_active_at'
    ];

    protected $hidden = [
        'deleted_at'
    ];
}
