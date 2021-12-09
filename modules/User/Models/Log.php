<?php


namespace Modules\User\Models;


use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Services\UserIpService;

class Log extends Model
{
    public $table='mttl_user_admin_log';

    public $guarded=[];

    public static $state=[
        1=>'信息',
        2=>'权限',
        3=>'资产'
    ];

    //添加日志
    public static function addLog($admin,$uid,$type,$log){
        if (empty($admin)) throw new \Exception('请刷新页面!');
        Log::query()->insert([
            'admin_id'=>$admin??1,
            'user_id'=>$uid,
            'type'=>$type,
            'log'=>$log,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    public function admin(){
        return $this->hasOne(AdminUser::class,'id','admin_id');
    }

    public function user(){
        return $this->hasOne(ProjectUser::class,'user_id','user_id');
    }
}
