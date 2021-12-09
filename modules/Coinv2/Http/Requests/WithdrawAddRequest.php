<?php

namespace Modules\Coinv2\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawAddRequest extends FormRequest
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
            'to'           => 'required|string',
            'symbol'       => 'required|string',
            'chain'       => 'required|string',
            'num'          => 'required|numeric|min:0',
            'memo'         => 'string',
            'pay_password' => 'string' // TODO  增加支付密码
        ];
    }
}
