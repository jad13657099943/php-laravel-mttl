<?php

namespace Modules\Coin\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;

class CoinSystemWallet extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    /**
     * 冷钱包
     */
    const LEVEL_COLD_WALLET = 0;
    /**
     * 温钱包
     */
    const LEVEL_WARM_WALLET = 1;
    /**
     * 热钱包
     */
    const LEVEL_HOT_WALLET = 2;
    /**
     * 钱包业务类型
     */
    const TYPE_GAS = 'gas';
    /**
     * 钱包业务类型
     */
    const TYPE_WITHDRAW = 'withdraw';

    /**
     * 钱包业务类型:归集钱包
     */
    const TYPE_POOLING_COLD = 'pooling_cold';

    public $table = 'coin_system_wallet';

    protected $fillable = [
        'chain',
        'address',
        'level',
        'remark',
        'gas_balance',
        'notice_max',
        'notice_min',
        'notice',
        'is_tokenio',
        'type',
        'tokenio_version'
    ];


    public static $typeList = [
        self::TYPE_GAS => "补gas钱包",
        self::TYPE_WITHDRAW => '支出热钱包',
        self::TYPE_POOLING_COLD => '归集冷钱包'
    ];

    public static $levelList = [
        self::LEVEL_COLD_WALLET => '冷钱包',
        self::LEVEL_WARM_WALLET => '温钱包',
        self::LEVEL_HOT_WALLET => '热钱包'
    ];

    public static $tokenioVersionList = [
        1 => 'V1',
        2=> 'V2',
    ];
}
