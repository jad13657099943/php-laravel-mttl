<?php

namespace Modules\Coin\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Coin\Components\TokenIO\TokenIO;
use Modules\Coin\Exceptions\CoinException;
use Modules\Core\Models\Casts\Upper;
use Modules\Coin\Services\CoinService;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\Core\Models\Traits\DynamicRelationship;

class Coin extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    /**
     * 启用
     */
    const STATUS_ENABLED = 1;
    /**
     * 关闭
     */
    const STATUS_DISABLED = 0;
    /**
     * 允许充值
     */
    const RECHARGE_STATE_OPEN = 1;
    /**
     * 关闭充值
     */
    const RECHARGE_STATE_CLOSE = 0;
    /**
     * 允许提现
     */
    const WITHDRAW_STATE_OPEN = 1;
    /**
     * 关闭提现
     */
    const WITHDRAW_STATE_CLOSE = 0;
    /**
     * 允许内部转账
     */
    const INTERNAL_STATE_OPEN = 1;
    /**
     * 关闭内部转账
     */
    const INTERNAL_STATE_CLOSE = 0;

    public $table = 'coin';

    protected $fillable = [
        'chain',
        'symbol',
        'coin',
        'short_name',
        'full_name',
        'real_symbol',
        'unique',
        'decimals',
        'hot_max',
        'withdraw_min',
        'withdraw_max',
        'recharge_state',
        'status',
        'cold_min',
        'comment',
        'contract_hash',
        'unit_decimals',
        'internal_state',
        'gas_price',
        'icon',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'chain' => Upper::class,
        'symbol' => Upper::class,
        'coin' => Upper::class,
        'gas_price' => 'double'
    ];

    public function getPriceAttribute()
    {
        return resolve(CoinService::class)->getPriceBySymbol($this->symbol);
    }

    /**
     * 查询启用状态
     *
     * @param $query
     * @param bool $enabled
     *
     * @return mixed
     */
    public function scopeWhereEnabled($query, $enabled = true)
    {
        return $query->where('status', $enabled ? static::STATUS_ENABLED : static::STATUS_DISABLED);
    }

    /**
     * 是否允许充值数
     *
     * @param $value
     * @param bool $exception
     *
     * @return bool
     * @throws CoinException
     */
    public function canRechargeWith($value, $exception = true)
    {
        if (!$this->canRecharge($exception) || $value < $this->recharge_min) {
            if ($exception) {
                throw new CoinException(trans('低于最小充值额度:' . $this->recharge_min));
            }
        }

        return true;
    }

    /**
     * 是否允许充值
     *
     * @param bool $exception
     *
     * @return bool
     * @throws CoinException
     */
    public function canRecharge($exception = true)
    {
        if (!$this->isEnabled() || $this->recharge_state != static::RECHARGE_STATE_OPEN) {
            if ($exception) {
                throw new CoinException(trans($this->symbol . '暂停充值'));
            }

            return false;
        }

        return true;
    }

    /**
     * 是否启用状态
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->status == static::STATUS_ENABLED;
    }

    /**
     * 是否以太坊链
     *
     * @return string
     */
    public function isChainETH()
    {
        return strtoupper($this->chain) == 'ETH';
    }

    /**
     * @param $num
     *
     * @return float|int
     */
    public function getWithdrawFeeWith($num)
    {
        return $num * ($this->withdraw_fee / 100);
    }

    /**
     * 计算手续费
     *
     * @param $num
     *
     * @return array
     */
    public function calcWithdrawFeeWith($num)
    {
        $fee = 0;
        if ($this->withdraw_fee > 0) {
            $fee = bcmul($num, $this->withdraw_fee / 100, 4);
            $num = $num > $fee ? $num - $fee : 0;
        }

        return [
            'fee' => $fee,
            'num' => $num,
        ];
    }

    /**
     * int 是否允许提现
     *
     * @return bool
     */
    public function canWithdraw($exception = true)
    {
        if (!$this->isEnabled() || $this->recharge_state != static::WITHDRAW_STATE_OPEN) {
            if ($exception) {
                throw new CoinException(trans($this->symbol . '暂停提现'));
            }

            return false;
        }

        return true;
    }

    /**
     * 是否允许提现数
     *
     * @param $value
     *
     * @return bool
     */
    public function canWithdrawWith($value, $exception = true)
    {
        if (!$this->canWithdraw($exception) || $value < $this->withdraw_min) {
            if ($exception) {
                throw new CoinException(trans('低于最小提现额度:' . $this->withdraw_min));
            }

            return false;
        }

        if ($value < ($fee = $this->getWithdrawFeeWith($value))) {
            if ($exception) {
                throw new CoinException(trans('当前提现额度不足以支付手续费:' . $fee));
            }
            return false;
        }

        return true;
    }

    /**
     * 是否达到冷钱包存储条件
     *
     * @param $value
     *
     * @return bool
     */
    public function canColdBalanceWith($value, $exception = true)
    {
        if (!$this->isEnabled() || $value < $this->cold_min) {
            if ($exception) {
                throw new CoinException(trans('低于最小归集额度:' . $this->cold_min));
            }

            return false;
        }

        return true;
    }

    public function checkAddress($address, $exception = false)
    {
        /** @var TokenIO $tokenIO */
        $tokenIO = resolve(TokenIO::class);
        $res = $tokenIO->chainCheck($this->symbol, $address);
        if (!$res || !($res['data'] ?? true)) {
            if ($exception) {
                throw new CoinException(trans('错误的'. $this->symbol .'地址:' . $address));
            }

            return false;
        }

        return true;
    }
}
