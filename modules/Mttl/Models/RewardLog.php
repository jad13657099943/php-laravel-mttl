<?php

namespace Modules\Mttl\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Casts\Translate;
use Modules\User\Models\ProjectUser;

class RewardLog extends Model
{
    protected $table = 'mttl_reward_log';

    protected $guarded = [];

    protected $casts = [
        'msg' => Translate::class,
        'extra' => 'array'
    ];

    /**
     * 推奖奖励
     */
    const TYPE_RECOMMEND = 'recommend';
    /**
     * 集结奖励
     */
    const TYPE_ASSEMBLY = 'assembly';
    /**
     * 级别奖励
     */
    const TYPE_LEVEL = 'level';
    /**
     * 平级奖励
     */
    const TYPE_PEER = 'peer';
    /**
     * 能量增幅
     */
    const TYPE_INCREASE = 'increase';

    public function user()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'user_id');
    }

    public function sourceUser()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'source_user');
    }
}
