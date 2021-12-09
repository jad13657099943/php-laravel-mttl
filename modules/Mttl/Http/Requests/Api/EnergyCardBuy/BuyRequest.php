<?php

namespace Modules\Mttl\Http\Requests\Api\EnergyCardBuy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Mttl\Models\EnergyCard;

class BuyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'crad_id' => [
                'numeric', 'required'
            ],
            'automatic' => [
                'boolean', 'required'
            ]
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
