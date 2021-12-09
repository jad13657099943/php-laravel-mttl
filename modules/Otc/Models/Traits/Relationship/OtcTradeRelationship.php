<?php


namespace Modules\Otc\Models\Traits\Relationship;


use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcTradeAppeal;
use Modules\Otc\Models\OtcUser;

trait OtcTradeRelationship
{
    public function otcTradeAppeal()
    {
        return $this->hasMany(OtcTradeAppeal::class, 'id', 'otc_trade_id');
    }

    public function otcExchange()
    {
        return $this->belongsTo(OtcExchange::class, 'otc_exchange_id', 'id');
    }

    public function buyerUser()
    {
        return $this->hasOne(OtcUser::class, 'user_id', 'buyer_id');
    }

    public function sellerUser()
    {
        return $this->hasOne(OtcUser::class, 'user_id', 'seller_id');
    }
}
