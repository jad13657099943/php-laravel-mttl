<?php


namespace Modules\Otc\Models\Traits\Attribute;


trait OtcTradeAppealAttribute
{
    public function getStatusTextAttribute()
    {
        return self::$statusMap[$this->status] ?? '未定义status';
    }

    public function getTypeTextAttribute()
    {
        return self::$typeMap[$this->type] ?? '未定义type';
    }

    public function getUserTypeTextAttribute()
    {
        return self::$userTypeMap[$this->user_type] ?? '未定义user_type';
    }
}
