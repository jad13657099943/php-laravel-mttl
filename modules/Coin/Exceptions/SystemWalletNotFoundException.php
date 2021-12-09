<?php

namespace Modules\Coin\Exceptions;

use Exception;

class SystemWalletNotFoundException extends Exception
{
    /**
     * @var mixed
     */
    public $data;

    public static function withData($data)
    {
        $message = 'System wallet not found.';

        return tap(new static($message), function ($exception) use ($data) {
            $exception->data = $data;
        });
    }
}
