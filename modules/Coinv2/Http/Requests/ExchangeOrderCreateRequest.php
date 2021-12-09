<?php

namespace Modules\Coinv2\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExchangeOrderCreateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'coin_get'     => 'required|string',
            'coin_pay'     => 'required|string',
            'pay_amount'   => 'required|numeric|min:0',
            'pay_password' => 'string' // TODO  增加支付密码
        ];
    }
}
