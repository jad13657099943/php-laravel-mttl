<?php

namespace Modules\Otc\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Otc\Models\OtcExchange;
use Symfony\Component\Console\Input\Input;

class OtcExchangeStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => [
                'required',
                Rule::in(array_keys(OtcExchange::$typeMap)),
                function ($field, $value, $fails) {
                    //如果是买单，用户必须填写支付方式
                    if (intval($value) === OtcExchange::TYPE_BUY) {
                        if (empty(\Request::get('bank_list'))) {
                            $fails(trans('otc::exception.付款支付方式有误'));
                        }
                    }
                }
            ],
            'num' => 'required|numeric|gt:0',
            'coin' => 'required|string',
            'min' => 'required|numeric|gt:0',
            'max' => 'required|numeric|gt:0',
            'price' => 'required|numeric|gt:0',
            'pay_password' => 'required',
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
