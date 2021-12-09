<?php

namespace Modules\Coin\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Casts\Upper;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\User\Models\ProjectUser;

class CoinAsset extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    public $table = 'coin_asset';

    protected $fillable = [
        'user_id',
        'symbol',
        'balance',
        'status'
    ];

    protected $casts = [
        'symbol' => Upper::class
    ];


    public function getBalanceAttribute($balance)
    {
        return floatval($balance);
    }


    public function coin()
    {
        return $this->belongsTo(Coin::class, 'symbol', 'symbol');
    }

    public function user()
    {
        return $this->belongsTo(ProjectUser::class, 'user_id', 'user_id');
    }

    public function getBalancePriceAttribute()
    {
        $coin = $this->coin;

        $data = [];
        if ($coin) { // 空代表没有匹配到coin记录
            $balance = $this->balance;

            foreach ($coin->price as $symbol => $price) {
                $data[$symbol] = number_format($balance * $price, $coin->decimals, '.', '');
            }
        }

        return $data;
    }

    /**
     * 查询启用状态
     *
     * @param $query
     * @param bool $enabled
     *
     * @return mixed
     */
    public function scopeWhereEnabled($query, $enabled = true)
    {
        return $query->where('status', $enabled ? static::STATUS_ENABLED : static::STATUS_DISABLED);
    }

    /**
     * 是否启用状态
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->status == static::STATUS_ENABLED;
    }

    /**
     * 判断该币种是否可启用
     * @param $balanceModel
     * @param bool $exception
     * @return bool
     */
    public function canChange($exception = true)
    {
        if (!$this->status) {
            if ($exception) {
                throw new \Exception('会员该币种未启用');
            }

            return false;
        }

        if (isset($this->frozen) && $this->frozen) {
            if ($exception) {
                throw new \Exception('会员该币种被禁用');
            }

            return false;
        }

        return true;
    }
}
