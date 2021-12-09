<?php

use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcUser;
use Modules\Otc\Exceptions\OtcTradeException;
use Modules\Otc\Exceptions\OtcExchangeException;
use Modules\Otc\Exceptions\OtcUserExchangeException;

if( !function_exists('generate_otc_no') ){
    function generate_otc_no($model)
    {
        $i      = 1;
        $max    = config("otc::config.create_no_max_times", 100);
        $prefix = date('YmdHis');
        while (true) {
            $no = $prefix . rand(100000, 999999);
            if( !$model::where('no', $no)->exists() ){
                return $no;
            } else if( $i > $max ){
                throw new UnexpectedValueException('Max generate no times.');
            }

            $i++;
        }
    }
}

if( !function_exists('with_otc_trade') ){

    function with_otc_trade($otcTradeOrId)
    {
        if( !$otcTradeOrId instanceof OtcTrade ){
            $otcTradeOrId = OtcTrade::where('id', $otcTradeOrId)->first();
            if( !$otcTradeOrId ){
                throw new OtcTradeException(trans('otc::exception.撮合交易不存在'));
            }
        }

        return $otcTradeOrId;
    }
}

if( !function_exists('with_otc_exchange') ){

    function with_otc_exchange($otcExchangeOrId)
    {
        if( !$otcExchangeOrId instanceof OtcExchange ){
            $otcExchangeOrId = OtcExchange::where('id', $otcExchangeOrId)->first();
            if( !$otcExchangeOrId ){
                throw new OtcExchangeException(trans('otc::exception.挂单交易不存在'));
            }
        }

        return $otcExchangeOrId;
    }
}


if( !function_exists('with_otc_user') ){

    function with_otc_user($otcUserOrId)
    {
        if( !$otcUserOrId instanceof OtcExchange ){
            $otcUserOrId = OtcUser::where('id', $otcUserOrId)->first();
            if( !$otcUserOrId ){
                throw new OtcUserExchangeException(trans('otc::exception.用户不存在'));
            }
        }

        return $otcUserOrId;
    }
}
