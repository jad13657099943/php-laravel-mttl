<?php

/**
 * This file is part of web3.php package.
 *
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 *
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Modules\Web3\Components\Web3\Formatters;

use InvalidArgumentException;
use Modules\Web3\Components\Web3\Utils;
use Modules\Web3\Components\Web3\Formatters\IFormatter;
use Modules\Web3\Components\Web3\Validators\TagValidator;
use Modules\Web3\Components\Web3\Formatters\QuantityFormatter;

class OptionalQuantityFormatter implements IFormatter
{
    /**
     * format
     *
     * @param mixed $value
     * @return string
     */
    public static function format($value)
    {
        if (TagValidator::validate($value)) {
            return $value;
        }
        return QuantityFormatter::format($value);
    }
}
