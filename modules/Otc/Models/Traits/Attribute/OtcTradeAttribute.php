<?php


namespace Modules\Otc\Models\Traits\Attribute;


use Modules\Otc\Models\OtcExchange;

trait OtcTradeAttribute
{
    public function getStatusTextAttribute()
    {
        return self::$statusMap[$this->status];
    }

    public function getTypeTextAttribute()
    {
        return self::$typeMap[$this->type];
    }

    public function getExchangeTypeTextAttribute($typeMap){

        $model = new OtcExchange();
        return $model::$typeMap[$this->exchange_type];
    }

    public function getNumAttribute($value)
    {
        return floatval($value);
    }


    public function getMaxAttribute($value)
    {
        return floatval($value);
    }

    public function getMinAttribute($value)
    {
        return floatval($value);
    }

}
