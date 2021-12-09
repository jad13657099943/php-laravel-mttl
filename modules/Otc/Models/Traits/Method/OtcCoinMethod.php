<?php


namespace Modules\Otc\Models\Traits\Method;


use Modules\Otc\Exceptions\OtcExchangeException;
use Modules\Otc\Models\OtcCoin;

trait OtcCoinMethod
{
    /**
     * 检查该币种是否可以交易
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->is_enable == self::ENABLE;;
    }

    /**
     * 检查该币种是否可以买入
     *
     * @param boolean $exception
     * @return boolean
     */
    public function canBuy($exception = true)
    {
        if (!$this->isEnable() || $this->buy != OtcCoin::BUY_ENABLE) {
            if ($exception) {
                throw new OtcExchangeException(trans('otc::exception.该币种暂时无法买入'));
            }

            return false;
        }

        return true;
    }

    /**
     * 检查该币种是否可以卖出
     *
     * @param array $options
     * @return bool
     * @throws OtcExchangeException
     */
    public function canSell($exception = true)
    {
        if (!$this->isEnable() || $this->sell != OtcCoin::SELL_ENABLE) {
            if ($exception) {
                throw new OtcExchangeException(trans('otc::exception.该币种暂时无法卖出'));
            }

            return false;
        }

        return true;
    }


    /**
     * 校验单笔最大最小交易量是否在合法范围
     *
     * @param $num
     * @param array $options
     * @return bool
     * @throws OtcExchangeException
     */
    public function checkNumRange($num, $exception = true)
    {
        if ($num < $this->min || $num > $this->max) {
            if ($exception) {
                throw new OtcExchangeException(trans('otc::exception.单笔交易数量超出范围',[
                    'min'=>$this->min,
                    'max'=>$this->max
                ]));
            }
            return false;
        }

        return true;
    }
}
