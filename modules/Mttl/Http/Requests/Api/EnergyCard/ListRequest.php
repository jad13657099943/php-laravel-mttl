<?php

namespace Modules\Mttl\Http\Requests\Api\EnergyCard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Mttl\Models\EnergyCard;

class ListRequest extends FormRequest
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
                "numeric",
                Rule::in(array_keys(EnergyCard::$typeMap))
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

    public function messages()
    {
        return [
            'type.in' => trans('mttl::validation.未知的能量卡类型')
        ];
    }
}
