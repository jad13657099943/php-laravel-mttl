<?php

namespace Modules\Otc\Models\Traits\Method;

use Modules\Otc\Exceptions\OtcExchangeException;
use Modules\Otc\Exceptions\OtcTradeException;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Models\OtcUser;

trait OtcExchangeMethod
{
    /**
     * 订单是否全部交易完成
     *
     * @return bool
     */
    public function isNumOver()
    {
        return bccomp($this->surplus, 0) === 0 && $this->unOverTradeCount() === 0;
    }

    /**
     * 交易未完成的撮合订单数量
     * @return mixed
     */
    public function unOverTradeCount()
    {
        return $this->otcTrade()->where('status', '<>', OtcExchange::STATUS_SUCCESS)->count();
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
     * 是否可以进行交易
     */
    public function canExchange($exception = true)
    {
        if ($this->isBuy()) {
            if ((!$this->otcCoin->canBuy(false) || !$this->otcUser->canBuy(false)) && $exception) {
                throw new OtcExchangeException(trans('otc::exception.当前挂单不允许购买'));
            }
        } else {
            if ((!$this->otcCoin->canSell(false) || !$this->otcUser->canSell(false)) && $exception) {
                throw new OtcExchangeException(trans('otc::exception.当前挂单不允许出售'));
            }
        }

        return true;
    }

    /**
     * 是否可以进行撮合交易
     *
     * @param $num
     *
     * @return bool
     * @throws OtcExchangeException
     * @throws OtcTradeException
     * @throws \Modules\Otc\Exceptions\OtcUserExchangeException
     */
    public function canTrade($num, $exception = true)
    {
        //状态、交易中、正常，两种状态下才可以进行交易
        if ($this->canExchanging($exception)) {

            //剩余可以交易数量是否足够
            if (!$this->isEnough($num)) {
                if ($exception) {
                    throw new OtcExchangeException(trans('otc::exception.可交易数量不足，无法交易'));
                }

                return false;
            }
        }

        //单笔交易最大值最小值范围
        return $this->checkNumRange($num, $exception);
    }

    /**
     * 是否在可以交易的状态
     *
     * @return bool
     */
    public function canExchanging($exception = false)
    {
        if (!$this->isExchanging()) {
            if ($exception) {
                throw new OtcExchangeException(trans('otc::exception.该挂单状态无法交易',[
                    'status'=>$this->statusText
                ]));
            }

            return false;
        }

        return true;
    }

    /**
     * 是否在交易状态
     * @return bool
     * @throws OtcExchangeException
     */
    public function isExchanging()
    {
        return in_array($this->statue, [static::STATUS_NORMAL, static::STATUS_SELLING]);
    }

    /**
     * 剩余可交易数量是否足够
     *
     * @param $num
     *
     * @return bool
     */
    public function isEnough($num)
    {
        return $this->surplus >= $num;
    }

    /**
     * 校验单笔最大最小交易量是否在合法范围
     *
     * @param $num
     * @param array $options
     *
     * @return bool
     * @throws OtcExchangeException
     */
    public function checkNumRange($num, $exception = true)
    {
        if ($num < $this->min || $num > $this->max) {
            if ($exception) {
                //throw new OtcExchangeException(trans('单笔交易数量超出范围: ' . $this->min . '-' . $this->max));
                throw new OtcExchangeException(trans('otc::exception.单笔交易数量超出范围',[
                    'min'=>$this->min,
                    'max'=>$this->max
                ]));
            }

            return false;
        }

        return true;
    }

    /**
     * 检查某个用户是否有该笔挂单撮合资格
     *
     * @param OtcUser $otcUser
     *
     * @return bool
     * @throws OtcTradeException
     * @throws \Modules\Otc\Exceptions\OtcUserExchangeException
     */
    public function canTradeByOtcUser(OtcUser $otcUser, $exception = true)
    {
        //自己的订单不能自己买
        if ($otcUser->user_id == $this->user_id) {
            if ($exception) {
                throw new OtcTradeException(trans('otc::exception.请勿提交自己的挂单交易'));
            }

            return false;
        }

        //挂单为买单，撮合订单应该为卖，所以检查用户是否可以卖
        //挂单为卖单，撮合订单应该为买，所以检查用户是否可以买
        return $this->isBuy() ? $otcUser->canSell($exception) : $otcUser->canBuy($exception);
    }

    /**
     * 确认挂单交易的数额是否正确
     *
     * @param bool $exception
     *
     * @return bool
     */
    public function ensureExchangeRight($exception = true)
    {
        //检查已成交数量+交易代付款数量+剩余数量  是否跟  挂单数量一致
        $total = $this->otcTrade()->where(function ($query) {
            //排除交易失败和取消两种状态
            $query->where('status', '<>', OtcTrade::STATUS_CANCEL)
                ->where('status', '<>', OtcTrade::STATUS_FAIL);
        })->sum('num');
        $total = $total + $this->surplus;

        if (bccomp($this->num, $total) !== 0) {
            if ($exception) {
                throw new OtcTradeException(trans('otc::exception.挂单交易金额错误'));
            }
        }

        return true;
    }


    /**
     * 扣除剩余可交易数量
     *
     * @param int $num
     *
     * @return $this
     */
    public function reduceSurplus($num = 0)
    {
        $this->surplus = $this->surplus - $num;

        return $this;
    }

    /**
     * 增加剩余可交易数量
     *
     * @param int $num
     *
     * @return $this
     */
    public function additionSurplus($num = 0)
    {
        $this->surplus = $this->surplus + $num;
        return $this;
    }

    /**
     * 下架取消
     *
     * @return $this
     */
    public function setCanceled()
    {
        $this->surplus = 0;
        $this->status = self::STATUS_CANCEL;

        return $this;
    }

    /**
     * 标记订单已完成
     *
     * @return $this
     */
    public function setSucceed()
    {
        $this->status = self::STATUS_SUCCESS;

        return $this;
    }

    /**
     * 设置状态为交易中
     *
     * @return $this
     */
    public function setSelling()
    {
        $this->status = OtcExchange::STATUS_SELLING;

        return $this;
    }
}
