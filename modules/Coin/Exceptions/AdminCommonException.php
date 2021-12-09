<?php


namespace Modules\Coin\Exceptions;

use Exception;
use Illuminate\Http\Request;

class AdminCommonException extends Exception
{
    public function __construct($message, int $code = 423)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request)
    {
        return response()->json(['message' => $this->message], $this->code);
    }
}
