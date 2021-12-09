<?php

namespace Modules\Coin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\ProjectUser;

class CoinInternalTrade extends Model
{
    public $table = 'coin_internal_trade';

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'symbol',
        'number',
        'get_num',
        'fee'
    ];

    /**
     * @param $number
     * @return float
     */
    public function getNumberAttribute($number){
        return floatval($number);
    }


    public function getGetNumAttribute($get_num){
        return floatval($get_num);
    }

    public function getFeeAttribute($fee){
        return floatval($fee);
    }


    /**
     * 关联查询转入会员
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function to_user(){
        return $this->hasOne(ProjectUser::class,'user_id','to_user_id');
    }

    /**
     * 关联查询转出会员
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function from_user(){
        return $this->hasOne(ProjectUser::class,'user_id','from_user_id');
    }
}
