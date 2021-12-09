<?php

namespace Modules\Otc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\Otc\Models\Traits\Attribute\OtcExchangeAttribute;
use Modules\Otc\Models\Traits\Method\OtcExchangeMethod;
use Modules\Otc\Models\Traits\Relationship\OtcExchangeRelationship;
use phpDocumentor\Reflection\Types\Self_;

class OtcExchange extends Model
{
    use SoftDeletes;

    use HasFail,
        HasTableName,
        DynamicRelationship;

    use OtcExchangeRelationship,
        OtcExchangeAttribute,
        OtcExchangeMethod;

    const TYPE_SELL = 1;
    const TYPE_BUY = 2;

    public static $typeMap = [
        self::TYPE_SELL => '卖单',
        self::TYPE_BUY => '买单'
    ];

    const STATUS_NORMAL = 0;
    const STATUS_SELLING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_CANCEL = 9;

    public static $statusMap = [
        self::STATUS_NORMAL => '正常交易',
        self::STATUS_SELLING => '交易中',
        self::STATUS_SUCCESS => '交易成功',
        self::STATUS_CANCEL => '交易取消'
    ];

    const BANK_TYPE_ALIPAY = 'alipay';
    const BANK_TYPE_WECHAT = 'wechat';
    const BANK_TYPE_BANK = 'bank';

    public static $bankTypeMap = [
        self::BANK_TYPE_WECHAT => '微信',
        self::BANK_TYPE_ALIPAY => '支付宝',
        self::BANK_TYPE_BANK => '银行卡',
    ];

    protected $fillable = [
        'id',
        'user_id',
        'no',
        'type',
        'num',
        'surplus',
        'coin',
        'price',
        'min',
        'max',
        'status',
        'bank_list',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'bank_list' => 'array'
    ];
}
