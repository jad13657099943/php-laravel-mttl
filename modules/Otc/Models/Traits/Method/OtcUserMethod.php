<?php

namespace Modules\Otc\Models\Traits\Method;

use Modules\Otc\Exceptions\OtcUserExchangeException;

trait OtcUserMethod
{
    /**
     * @param array $options
     * @return bool
     * @throws OtcUserExchangeException
     */
    public function canBuy($exception = true)
    {
        $isEnable = $this->enable_buy == self::BUY_ENABLE;
        if (!$isEnable && $exception) {
            throw new OtcUserExchangeException(trans('otc::exception.您暂时无法买入'));
        } else {
            return $isEnable;
        }
    }

    /**
     * @param bool $exception
     * @return bool
     * @throws OtcUserExchangeException
     */
    public function canSell($exception = true)
    {
        $isEnable = $this->enable_sell == self::SELL_ENABLE;

        if (!$isEnable && $exception) {
            throw new OtcUserExchangeException(trans('otc::exception.您暂时无法买入'));
        } else {
            return $isEnable;
        }
    }


    public function setSuccessBuy($count)
    {
        $this->success_buy = $count;
        return $this;
    }

    public function setSuccessSell($count)
    {
        $this->success_sell = $count;
        return $this;
    }

    public function setAverageTime($time)
    {
        $this->average_time = $time;
        return $this;
    }

}
