<?php


namespace Modules\User\Models;


use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    public $table = 'mttl_binance_api';

    public $guarded = [];

    public static $type = [
        '' => '',
        1 => '尚未激活',
        2 => '处理中',
        3 => '已经激活'
    ];

    public static $status=[
      1=>'关闭',
      2=>'开启'
    ];

    public function user()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'user_id');
    }
}
