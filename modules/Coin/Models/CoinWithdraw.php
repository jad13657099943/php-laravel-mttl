<?php

namespace Modules\Coin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Casts\Upper;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\User\Models\ProjectUser;

class CoinWithdraw extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    // -3=被撤销【已撤销】、-2=待确认(需人工审核)【待确认】、-1=已确认待处理【处理中】\n0广播完成.已生成哈希值【处理中】，1已打包【已打包】，2已完成.打包不可逆【已完成】
    /**
     * 被撤销【已撤销】
     */
    const STATE_CANCELED = -3;
    /**
     * 待确认(需人工审核)【待确认】
     */
    const STATE_HUMAN_TRANSFER = -2;
    /**
     * 已确认待处理【处理中】\n0广播完成.已生成哈希值【处理中】
     */
    const STATE_PROCESSING = -1;
    /**
     * 标记已记录
     */
    const STATE_LOGGED = 0;
    /**
     * 已打包【已打包】
     */
    const STATE_PACKAGED = 1;
    /**
     * 已完成.打包不可逆【已完成】
     */
    const STATE_FINISHED = 2;

    public $table = 'coin_withdraw';

    protected $fillable = [
        'user_id',
        'symbol',
        'to',
        'num',
        'state',
        'cost',
        'chain',
    ];

    protected $casts = [
        'symbol' => Upper::class
    ];


    /**
     * 关联获取会员信息
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function projectUser()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function coin()
    {
        return $this->hasOne(Coin::class, 'symbol', 'symbol');
    }

    public function needTrade()
    {
        return $this->state == static::STATE_PROCESSING && $this->state != static::STATE_LOGGED;
    }

    /**
     * 格式化数量
     * @return float
     */
    public function getNumAttribute($num)
    {
        return floatval($num);
    }

    /**
     * 返回状态对应的文字说明
     */
    public function getStateTextAttribute()
    {

        $text = '';
        switch ($this->state) {
            case self::STATE_CANCELED:
                $text = '被撤销';
                break;
            case self::STATE_HUMAN_TRANSFER:
                $text = '待审核';
                break;
            case self::STATE_PROCESSING:
            case self::STATE_PACKAGED:
                $text = '处理中';
                break;
            case self::STATE_LOGGED:
                $text = '广播完成,待确认';
                break;
            case self::STATE_FINISHED:
                $text = '已完成';
                break;
            default:
                $text = $this->state;
                break;
        }

        return $text;
    }

    /**
     * 大于等于此值时使用人工审核转账
     *
     * @return bool
     */
    public function needManually()
    {
        return $this->num > $this->coin->hot_max;
    }
}
