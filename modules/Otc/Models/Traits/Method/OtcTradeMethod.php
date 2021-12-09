<?php

namespace Modules\Otc\Models\Traits\Method;

use App\Models\User;
use Modules\Otc\Exceptions\OtcTradeException;

trait OtcTradeMethod
{
    /**
     * 更改状态为已支付待审核
     *
     * @return $this
     */
    public function setPaid($bank = null)
    {
        $this->paid_at = date('Y-m-d H:i:s');
        $this->status = self::STATUS_PAY;
        $this->bank = $bank;
        return $this;
    }

    /**
     * 更改状态为交易确认，已完成状态
     * @return $this
     */
    public function setConfirmed()
    {
        $this->confirmed_at = date('Y-m-d H:i:s');
        $this->status = self::STATUS_SUCCESS;
        return $this;
    }

    /**
     * @return $this
     */
    public function setSellerAppeal()
    {
        $this->status = self::STATUS_SELLER_APPEAL;
        return $this;
    }

    /**
     * @return $this
     */
    public function setBuyerAppeal()
    {
        $this->status = self::STATUS_BUYER_APPEAL;
        return $this;
    }

    /**
     * 超时，或者用户取消
     * @return $this
     */
    public function setCancel()
    {
        $this->status = self::STATUS_CANCEL;
        return $this;
    }

    /**
     * 设置状态为已冻结对应币种
     *
     * @return $this
     */
    public function setFrozen()
    {
        $this->status = self::STATUS_FROZEN;
        return $this;
    }

    /**
     * 是否支付
     * @return bool
     */
    public function isPaid()
    {
        return intval($this->status) < self::STATUS_PAY;
    }

    /**
     * 是否在待冻结对应币种状态
     *
     * @return bool
     */
    public function isWaitingFrozen($exception = true)
    {
        if (intval($this->status) !== self::STATUS_WAITING && $exception) {
            throw new OtcTradeException(trans("otc::exception.订单状态有误"));
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expired_at < date('Y-m-d H:i:s');
    }

    public function isSell()
    {
        return intval($this->type) === self::TYPE_SELL;
    }

    public function isBuy()
    {
        return intval($this->type) === self::TYPE_BUY;
    }


    /**
     * 是否已支付待确认状态
     * @return bool
     */
    public function isWaitingConfirm()
    {
        return intval($this->status) === self::STATUS_PAY;
    }


    /**
     * 订单是否在冻结币种，待支付状态,
     *
     * @return bool
     */
    public function isWaitingPay()
    {
        return intval($this->status) === self::STATUS_FROZEN;
    }

    /**
     * 校验用户是否可以支付
     *
     * @param $user
     * @return bool
     */
    public function canPayByUser($user, $exception = true)
    {
        if (!$this->isWaitingPay()) {
            if ($exception) {
                throw new OtcTradeException(trans('otc::exception.订单非待支付状态'));
            }
            return false;

        }

        if (!$this->isPaid()) {
            if ($exception) {
                throw new OtcTradeException(trans('otc::exception.订单已支付请勿重复支付'));
            }
            return false;

        }

        if ($this->isExpired()) {
            if ($exception) {
                throw new OtcTradeException(trans('otc::exception.订单已超时'));
            }
            return false;

        }

        if ($user->id !== $this->buyer_id) {
            if ($exception) {
                throw new OtcTradeException(trans('otc::exception.您无法支付该订单'));
            }
            return false;
        }

        return true;
    }

    /**
     * 校验用户是否可以确认
     *
     * @param $user
     * @return bool
     */
    public function canConfirmByUser(User $user)
    {
        return $user->id === $this->seller_id;
    }


    /**
     * 获取用户在当前订单下身份，是买家还是卖家
     *
     * @param User $user
     * @param bool $exception
     * @return bool
     * @throws OtcTradeException
     */
    public function userIdentity(User $user, $exception = true)
    {
        if ($this->buyer_id == $user->id) {
            return self::TYPE_BUY;
        } else if ($this->seller_id == $user->id) {
            return self::TYPE_SELL;
        }
        if ($exception) {
            throw new OtcTradeException(trans("otc::exception.你没有权限操作该订单"));
        }
        return false;
    }


}
