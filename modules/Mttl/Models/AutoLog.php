<?php


namespace Modules\Mttl\Models;


use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\ProjectUser;

class AutoLog extends Model
{
    public $table='mttl_auto_log';

    public $guarded=[];


    public static $state=[
      1=>'自动设置'
    ];

    public static function addLog($uid,$log,$type=1){
        $state=[
            true=>'开启',
            false=>'关闭'
        ];
        self::query()->insert([
           'user_id'=>$uid,
            'type'=>$type,
            'log'=>$state[$log],
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    public function user(){
        return $this->hasOne(ProjectUser::class,'user_id','user_id');
    }
}
