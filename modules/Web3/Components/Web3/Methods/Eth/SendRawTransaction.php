<?php

/**
 * This file is part of web3.php package.
 *
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 *
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Modules\Web3\Components\Web3\Methods\Eth;

use InvalidArgumentException;
use Modules\Web3\Components\Web3\Methods\EthMethod;
use Modules\Web3\Components\Web3\Validators\HexValidator;
use Modules\Web3\Components\Web3\Formatters\HexFormatter;

class SendRawTransaction extends EthMethod
{
    /**
     * validators
     *
     * @var array
     */
    protected $validators = [
        HexValidator::class
    ];

    /**
     * inputFormatters
     *
     * @var array
     */
    protected $inputFormatters = [
        HexFormatter::class
    ];

    /**
     * outputFormatters
     *
     * @var array
     */
    protected $outputFormatters = [];

    /**
     * defaultValues
     *
     * @var array
     */
    protected $defaultValues = [];

    /**
     * construct
     *
     * @param string $method
     * @param array $arguments
     * @return void
     */
    // public function __construct($method='', $arguments=[])
    // {
    //     parent::__construct($method, $arguments);
    // }
}
