<?php

namespace Modules\Coin\Models;

use Illuminate\Database\Eloquent\Model;

class CoinExchangeSettings extends Model
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    public $table = 'exchange_settings';

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
}
