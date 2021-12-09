<?php


namespace Modules\User\Models;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserAppeal extends Model
{
    protected $table = 'user_appeal';

    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    public static $typeList = [
        'app' => '系统问题',
        'reward' => '奖励问题'
    ];

    public static $stateMap = [
        0 => '未处理',
        1 => '处理中',
        2 => '已处理'
    ];

    public function getTypeTextAttribute()
    {
        return self::$typeList[$this->attributes['type']] ?? '';
    }

    public function getStateTextAttribute()
    {
        return self::$stateMap[$this->attributes['state']] ?? '未定义state';
    }

    public function user()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'user_id');
    }

}
