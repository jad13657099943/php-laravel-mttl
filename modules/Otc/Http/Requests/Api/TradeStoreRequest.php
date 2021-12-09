<?php

namespace Modules\Otc\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TradeStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'otc_exchange_id' => 'required|exists:otc_exchanges,id',
            'num' => 'required|numeric',
            'pay_password' => 'required|size:6'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
