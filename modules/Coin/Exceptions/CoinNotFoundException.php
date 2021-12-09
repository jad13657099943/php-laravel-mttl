<?php

namespace Modules\Coin\Exceptions;

use Exception;

class CoinNotFoundException extends Exception
{

    public static function withSymbol($symbol)
    {
        $message = 'Coin with symbol: ' . $symbol . ' not found.';

        return new static($message);
    }
}
