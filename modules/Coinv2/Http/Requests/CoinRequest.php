<?php


namespace Modules\Coinv2\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class CoinRequest  extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'withdraw_min' => 'required|numeric|min:0',
            'withdraw_max' => 'required|numeric|min:0',
            'withdraw_fee' => 'required|numeric|min:0',
            'gas_price' => 'required|numeric|min:0',
            'recharge_state' => 'required',
            'recharge_min' => 'required|numeric|min:0',
            'cold_min' => 'required|numeric|min:0',
            'internal_state' => 'required',
        ];
    }
}
