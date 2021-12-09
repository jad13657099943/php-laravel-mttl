<?php


namespace Modules\Coin\Http\Requests;


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
            'is_tokenio' => 'required',
            'type' => 'required',
            'tokenio_version'=>'required',
        ];
    }

}
