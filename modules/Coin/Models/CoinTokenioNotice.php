<?php

namespace Modules\Coin\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;

class CoinTokenioNotice extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    /**
     * 待处理
     */
    const STATE_PROCESSING = 0;
    /**
     * 已处理
     */
    const STATE_PROCESSED = 1;
    /**
     * 不需要处理
     */
    const STATE_DONT_PROCESS = -1;
    /**
     * 通知类型: 充值
     */
    const TYPE_CHAIN_TO = 'chain.to';
    /**
     * 通知类型: 提现
     */
    const TYPE_CHAIN_FROM = 'chain.from';
    /**
     * 确认中(可逆)
     */
    const DATA_TX_STATE_CONFIRM = 1;
    /**
     * 交易成功状态(不可逆)
     */
    const DATA_TX_STATE_SUCCESS = 2;
    /**
     * @var string
     */
    public $table = 'coin_tokenio_notice';

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'nonce',
        'data',
        'state',
        'version',
    ];

    /**
     *
     * @var array
     */
    protected $casts = [
        'data' => 'json',
    ];

    /**
     * 是否充值通知
     *
     * @return bool
     */
    public function isRecharge()
    {
        return $this->type == static::TYPE_CHAIN_TO;
    }

    /**
     * 是否提现通知
     *
     * @return bool
     */
    public function isWithdraw()
    {
        return $this->type == static::TYPE_CHAIN_FROM;
    }

    /**
     * 是否交易成功(已打包)
     *
     * @return bool
     */
    public function isDataSucceeded()
    {
        $txState = $this->data['tx_state'] ?? false;

        return in_array($txState, [static::DATA_TX_STATE_CONFIRM, static::DATA_TX_STATE_SUCCESS]);
    }

    /**
     * 是否需要处理
     *
     * @return bool
     */
    public function needProcess()
    {
        return $this->sate == static::STATE_DONT_PROCESS || $this->state == static::STATE_PROCESSED;
    }

    /**
     * 设置为无需处理
     */
    public function setDontProcess()
    {
        $this->state = static::STATE_DONT_PROCESS;

        return $this;
    }

    /**
     * 设置为已处理
     */
    public function setProcessed()
    {
        $this->state = static::STATE_PROCESSED;

        return $this;
    }
}
