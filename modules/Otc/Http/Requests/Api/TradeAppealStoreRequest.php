<?php

namespace Modules\Otc\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TradeAppealStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//            'otc_trade_id' => 'required|exists:otc_trades,id',
            'reason' => 'required|string|max:512',
            'image_list' => 'required|array'
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
