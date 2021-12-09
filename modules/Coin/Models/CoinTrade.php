<?php

namespace Modules\Coin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Coin\Exceptions\CoinException;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\User\Models\ProjectUser;

class CoinTrade extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    /**
     * 余额不足
     */
    const STATE_NOT_SUFFICIENT_FUNDS = -4;
    /**
     * 被取消
     */
    const STATE_CANCELED = -3;
    /**
     * 人工转账
     */
    const STATE_HUMAN_TRANSFER = -2;
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
    /**
     * 归集冷钱包
     */
    const ACTION_POOLING_COLD = 'pooling_cold';
    /**
     * 补充gas
     */
    const ACTION_GAS = 'gas';
    /**
     * 提现
     */
    const ACTION_WITHDRAW = 'withdraw';

    /**
     * FIL固定gas数量
     */
    const FIL_GAS_NUM = 0.05;

    public $table = 'coin_trade';

    public $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'num' => 'double',
    ];

    public function coinLogModule()
    {
        return $this->belongsTo(CoinLogModules::class, 'module', 'module')
            ->where('action', $this->action);
    }

    /**
     * 关联会员
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coin()
    {
        return $this->belongsTo(Coin::class, 'symbol', 'symbol');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeWhereNeedProcess($query)
    {
        return $query->whereIn('state', [static::STATE_PROCESSING, static::STATE_NOT_SUFFICIENT_FUNDS]);
    }

    /**
     * 是否需要处理
     *
     * @return bool
     */
    public function needProcess()
    {
        return in_array($this->state, [static::STATE_PROCESSING, static::STATE_NOT_SUFFICIENT_FUNDS]);
    }

    /**
     * 是否归集交易类型
     *
     * @return bool
     */
    public function isPoolingCold()
    {
        return $this->action == static::ACTION_POOLING_COLD;
    }

    /**
     * 补充GAS
     *
     * @return string
     */
    public function isGas()
    {
        return $this->action == static::ACTION_GAS;
    }

    /**
     * 是否提现
     *
     * @return bool
     */
    public function isWithdraw()
    {
        return $this->action == static::ACTION_WITHDRAW;
    }

    /**
     * 交易是否完成
     *
     * @return bool
     */
    public function hasFinished()
    {
        return in_array($this->state, [static::STATE_FINISHED, self::STATE_PACKAGED]);
    }

    /**
     * 是否达到归集条件
     *
     * @return bool
     */
    public function canPoolingColdWith($balance, $exception = true)
    {
        if ($balance < $this->coin->cold_min) {
            if ($exception) {
                throw new CoinException(trans('低于归集额度:' . $this->coin->cold_min));
            }

            return false;
        }

        return true;
    }

    /**
     * TODO 优化减少查询
     *
     * @return mixed|string
     */
    public function getModuleActionNameAttribute()
    {
        $coinLogModule = $this->coinLogModule()->where('action', $this->attributes['action'])->first();

        return empty($coinLogModule) ? "" : $coinLogModule->title;
    }

    /**
     * 返回状态说明
     * @return mixed|string
     */
    public function getStateTextAttribute()
    {

        $text = '';
        switch ($this->state) {
            case self::STATE_NOT_SUFFICIENT_FUNDS:
                $text = '余额不足';
                break;

            case self::STATE_CANCELED:
                $text = '被撤销';
                break;
            case self::STATE_HUMAN_TRANSFER:
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
