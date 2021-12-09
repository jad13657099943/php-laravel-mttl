<?php


namespace Modules\Coinv2\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class SystemWalletRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'chain' => 'required',
            'level' => 'required',
            'remark' => 'required',
            'notice_max' => 'required|numeric',
            'notice_min' => 'required|numeric',
            'notice' => 'required|email',
            'is_tokenio' => 'required',
            'type' => 'required'
        ];
    }

}
