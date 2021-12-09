<?php

namespace Modules\Otc\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Otc\Models\OtcTrade;

class TradeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sort' => 'in:asc,desc',
            'orderBy' => 'in:price,min,max,num',
            'type' => [
                Rule::in(array_keys(OtcTrade::$typeMap)),
            ],
            'status' => [
                Rule::in(array_keys(OtcTrade::$typeMap)),
            ],
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
