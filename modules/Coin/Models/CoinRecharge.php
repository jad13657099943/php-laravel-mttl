<?php

namespace Modules\Coin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\User\Models\ProjectUser;

class CoinRecharge extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    /**
     * 被撤销【已撤销】
     */
    const STATE_REVOCATION = -3;
    /**
     * 待确认(需人工审核)【待确认】
     */
    const STATE_CONFIRMING = -2;
    /**
     * 已确认待处理【处理中】
     */
    const STATE_PROCESSING = -1;
    /**
     * 广播完成.已生成哈希值【处理中】
     */
    const STATE_BROADCASTED = 0;
    /**
     * 已打包【已打包】
     */
    const STATE_PACKAGED = 1;
    /**
     * 已完成.打包不可逆【已完成】
     */
    const STATE_FINISHED = 2;

    public $table = 'coin_recharge';

    protected $fillable = [
        'user_id',
        'symbol',
        'from',
        'to',
        'value',
        'memo',
        'hash',
        'state',
        'block_number',
        'packaged_at',
        'chain',
    ];


    /**
     * 关联获取订单信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function projectUser()
    {
        return $this->hasOne(ProjectUser::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function coin()
    {
        return $this->hasOne(Coin::class, 'symbol', 'symbol');
    }

    /**
     * 是否完成
     *
     * @return bool
     */
    public function hasFinished()
    {
        return in_array($this->state, [static::STATE_PACKAGED, self::STATE_FINISHED]);
    }

    /**
     * 是否需要归集
     *
     * @return bool
     */
    public function needPoolingCold()
    {
        //return $this->hasFinished() && $this->value >= $this->coin->cold_min;
        //这里不判断数量是否满足设置的币种归集数量，因为最终归集的是这个地址的链上余额
        return $this->hasFinished();
    }

    /**
     * 格式化数量
     * @return float
     */
    public function getValueAttribute($value)
    {
        return floatval($value);
    }

    /**
     * 返回状态对应的文字说明
     */
    public function getStateTextAttribute()
    {

        $text = '';
        switch ($this->state) {
            case self::STATE_REVOCATION:
                $text = '被撤销';
                break;
            case self::STATE_CONFIRMING:
                $text = '待确认(需人工审核)';
                break;
            case self::STATE_PROCESSING:
                $text = '已确认待处理【处理中】';
                break;
            case self::STATE_BROADCASTED:
                $text = '广播完成【处理中】';
                break;
            case self::STATE_PACKAGED:
                $text = '已打包【已打包】';
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


}
