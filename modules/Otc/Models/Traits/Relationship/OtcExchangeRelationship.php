<?php


namespace Modules\Otc\Models\Traits\Relationship;


use App\Models\User;
use Modules\Coin\Models\Coin;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Models\OtcTradeAppeal;
use Modules\Otc\Models\OtcUser;

trait OtcExchangeRelationship
{
    /**
     * @return mixed
     */
    public function otcTrade()
    {
        return $this->hasMany(OtcTrade::class, 'otc_exchange_id', 'id');
    }

    public function otcTradeAppeal()
    {
        return $this->hasMany(OtcTradeAppeal::class, 'id', 'otc_exchange_id');
    }

    /**
     * OtcUser 表
     *
     * @return mixed
     */
    public function otcUser()
    {
        return $this->belongsTo(OtcUser::class, 'user_id', 'user_id');
    }

    /**
     * User表，关联Core模块
     *
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 关联的是Coin模块Coin表
     *
     * @return mixed
     */
    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin', 'coin');
    }

}
