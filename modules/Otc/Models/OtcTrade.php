<?php

namespace Modules\Otc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\Otc\Models\Traits\Attribute\OtcTradeAttribute;
use Modules\Otc\Models\Traits\Method\OtcTradeMethod;
use Modules\Otc\Models\Traits\Relationship\OtcTradeRelationship;

class OtcTrade extends Model
{
    use SoftDeletes;

    use HasFail,
        HasTableName,
        DynamicRelationship;

    use OtcTradeRelationship,
        OtcTradeMethod,
        OtcTradeAttribute;

    const TYPE_SELL = 1;
    const TYPE_BUY = 2;

    public static $typeMap = [
        self::TYPE_SELL => '卖单',
        self::TYPE_BUY => '买单'
    ];


    const STATUS_FAIL = -1;
    const STATUS_WAITING = 0;
    const STATUS_FROZEN = 1;
    const STATUS_PAY = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_BUYER_APPEAL = 8;
    const STATUS_SELLER_APPEAL = 9;
    const STATUS_CANCEL = 10;

    public static $statusMap = [
        self::STATUS_FAIL => '退回失败',
        self::STATUS_WAITING => '待扣币',//待扣除币种
        self::STATUS_FROZEN => '待支付',//已冻结，待支付
        self::STATUS_PAY => '已付款待确认',
        self::STATUS_SUCCESS => '交易完成',
        self::STATUS_BUYER_APPEAL => '买家申诉',
        self::STATUS_SELLER_APPEAL => '卖家申诉',
        self::STATUS_CANCEL => '取消',
    ];
    protected $fillable = [
        'otc_exchange_id',
        'no',
        'exchange_type',
        'type',
        'buyer_id',
        'seller_id',
        'coin',
        'num',
        'price',
        'min',
        'max',
        'status',
        'reason',
        'paid_at',
        'expired_at',
        'confirmed_at',
        'bank_list',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'bank' => 'array'
    ];
}
