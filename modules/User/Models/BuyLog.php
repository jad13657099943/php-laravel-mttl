<?php


namespace Modules\User\Models;


use Illuminate\Database\Eloquent\Model;

class BuyLog extends Model
{
    public $table='mttl_buy_log';
    public $guarded=[];

    public static $state=[
      1=>'转换矩阵'
    ];

    public static function addLog($uid,$log,$type=1){
        self::query()->insert(['user_id'=>$uid,'type'=>$type,'log'=>$log,'created_at'=>date('Y-m-d H:i:s')]);
    }

    public function user(){
        return $this->hasOne(ProjectUser::class,'user_id','user_id');
    }
}
