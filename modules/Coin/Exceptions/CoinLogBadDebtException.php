<?php

namespace Modules\Coin\Exceptions;

use Exception;
use Modules\Coin\Services\BalanceChangeService;
use Throwable;

/**
 *  Coin Log表记录数据不一致异常
 * @package Modules\Coin\Exceptions
 */
class CoinLogBadDebtException extends Exception
{
    /**
     * @var BalanceChangeService
     */
    public $balanceChanger;

    /**
     * Create a new exception instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @param  \Symfony\Component\HttpFoundation\Response|null $response
     * @param  string $errorBag
     *
     * @return void
     */
    public function __construct(BalanceChangeService $balanceChanger, $code = 0, Throwable $previous = null)
    {
        parent::__construct('用户余额记录异常.', $code, $previous);

        $this->balanceChanger = $balanceChanger;
    }
}
