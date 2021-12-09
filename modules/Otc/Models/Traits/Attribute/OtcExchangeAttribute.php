<?php


namespace Modules\Otc\Models\Traits\Attribute;


trait OtcExchangeAttribute
{
    public function getStatusTextAttribute()
    {
        return static::$statusMap[$this->status];
    }

    public function getTypeTextAttribute()
    {
        return static::$typeMap[$this->type];
    }

    /**
     * 解析收款方式
     * @return array
     */
    public function getBankListTextAttribute()
    {

        $data = $this->bank_list;
        $arr = [];
        foreach ($data as $item) {
            if (isset(static::$bankTypeMap[$item])) {
                $arr[] = static::$bankTypeMap[$item];
            }
        }
        return $arr;
    }

    public function getNumAttribute($value)
    {
        return floatval($value);
    }

    public function getSurplusAttribute($value)
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
