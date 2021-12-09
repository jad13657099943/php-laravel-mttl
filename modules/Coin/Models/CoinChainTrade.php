<?php

namespace Modules\Coin\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Casts\Upper;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;

class CoinChainTrade extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    /**
     * 提现类型
     */
    const TYPE_WITHDRAW = 'withdraw';
    /**
     * 充值类型
     */
    const TYPE_RECHARGE = 'recharge';

    public $table = 'coin_trade';

    protected $fillable = [
        'user_id',
        'symbol',
        'from',
        'to',
        'memo',
        'type',
        'no',
        'num',
        'gas_price',
        'hash',
        'transfer_type',
        'weight',
        'block_number',
        'state',
        'sate_info',
        'withdraw_at',
        'packaged_at',
    ];

    protected $casts = [
        'symbol' => Upper::class,
    ];
}
