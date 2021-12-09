<?php


namespace Modules\User\Models;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Frontend\UserInvitation;

class ProjectUser extends Model
{

    public $table = 'project_user';
    public $guarded = [];

    /**
     * 应进行类型转换的属性
     *
     * @var string[]
     */
    protected $casts = [
        'authority' => 'array'
    ];

    public static $gradeMap = [
        0 => '普通会员',
        1 => 'V1(火)',
        2 => 'V2(地)',
        3 => 'V3(风)',
        4 => 'V4(水)',
        5 => 'V5(以太)'
    ];

    public static $type=[
       1=>'关闭',
       2=>'开启',
       3=>'已绑定'
    ];

    /**
     * 登录权限
     */
    const AUTHORITY_LOGIN = 'login';
    /**
     * 静态奖励权限
     */
    const AUTHORITY_STATIC = 'static';
    /**
     * 动态奖励权限
     */
    const AUTHORITY_DYNAMIC = 'dynamic';
    /**
     * 内部转账权限
     */
    const AUTHORITY_TRANSFER = 'transfer';
    /**
     * 空单发放动态奖励权限
     */
    const AUTHORITY_EMPTY = 'empty';

    public static $authorityMap = [
        self::AUTHORITY_LOGIN => '登录权限',
        self::AUTHORITY_STATIC => '静态奖励权限',
        self::AUTHORITY_DYNAMIC => '动态奖励权限',
        self::AUTHORITY_TRANSFER => '内部转账权限',
        self::AUTHORITY_EMPTY => '空单发放动态奖励权限'
    ];

    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->wasChanged('show_userid')) {
                // 修改邀请码
                UserInvitation::query()
                    ->where('used_user_id', 0)
                    ->where('user_id', $user->user_id)
                    ->update(['token' => $user->show_userid]);
            }
        });
    }

    public function getGradeTextAttribute()
    {
        return self::$gradeMap[$this->attributes['grade']] ?? $this->attributes['grade'];
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function parent()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'parent_id');
    }

    public function key(){
        return $this->hasOne(Key::class,'user_id','user_id');
    }
}
