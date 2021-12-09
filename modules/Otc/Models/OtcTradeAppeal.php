<?php

namespace Modules\Otc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\Otc\Models\Traits\Attribute\OtcTradeAppealAttribute;
use Modules\Otc\Models\Traits\Method\OtcTradeAppealMethod;
use Modules\Otc\Models\Traits\Relationship\OtcTradeAppealRelationship;

class OtcTradeAppeal extends Model
{
    use SoftDeletes;

    use HasFail,
        HasTableName,
        DynamicRelationship;

    use OtcTradeAppealRelationship,
        OtcTradeAppealAttribute,
        OtcTradeAppealMethod;

    const TYPE_SELL = 1;
    const TYPE_BUY = 2;

    public static $typeMap = [
        self::TYPE_SELL => '卖单',
        self::TYPE_BUY => '买单'
    ];

    const USER_TYPE_SELLER = 1;
    const USER_TYPE_BUYER = 2;

    public static $userTypeMap = [
        self::USER_TYPE_SELLER => '卖家',
        self::USER_TYPE_BUYER => '买家'
    ];
    const STATUS_WAITING = -1;
    const STATUS_REJECT = 1;
    const STATUS_SUCCESS = 2;

    public static $statusMap = [
        self::STATUS_WAITING => '待处理',
        self::STATUS_REJECT => '已驳回',
        self::STATUS_SUCCESS => '已完成'
    ];
    protected $fillable = [
        'otc_trade_id',
        'user_id',
        'no',
        'type',
        'user_type',
        'image_list',
        'reason',
        'handle',
        'status'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'image_list' => 'array'
    ];
}
