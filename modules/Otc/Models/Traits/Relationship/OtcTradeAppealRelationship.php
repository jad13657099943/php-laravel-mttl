<?php


namespace Modules\Otc\Models\Traits\Relationship;


use App\Models\User;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Models\OtcUser;

trait OtcTradeAppealRelationship
{
    public function otcExchange()
    {
        return $this->belongsTo(OtcExchange::class, 'id', 'otc_exchange_id');
    }

    public function otcTrade()
    {
        return $this->belongsTo(OtcTrade::class, 'id', 'otc_trade_id');
    }

    /**
     * OtcUser 表
     *
     * @return mixed
     */
    public function otcUser()
    {
        return $this->belongsTo(OtcUser::class,'user_id','user_id');
    }

    /**
     * User表，关联Core模块
     *
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
