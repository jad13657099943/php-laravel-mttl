<?php


namespace Modules\Otc\Models\Traits\Relationship;


use App\Models\User;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Models\OtcTradeAppeal;

trait OtcUserRelationship
{

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


    public function otcExchange()
    {
        return $this->hasMany(OtcExchange::class, 'user_id', 'user_id');
    }

    public function otcTrade()
    {
        return $this->hasMany(OtcTrade::class, 'user_id', 'user_id');
    }

    public function otcTradeAppeal()
    {
        return $this->hasMany(OtcTradeAppeal::class, 'user_id', 'user_id');
    }
}
