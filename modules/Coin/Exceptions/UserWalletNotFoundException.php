<?php

namespace Modules\Coin\Exceptions;

use Exception;

class UserWalletNotFoundException extends Exception
{
    /**
     * @var mixed
     */
    public $data;

    public static function withData($data)
    {
        $message = 'User wallet not found.';

        return tap(new static($message), function ($exception) use ($data) {
            $exception->data = $data;
        });
    }
}
