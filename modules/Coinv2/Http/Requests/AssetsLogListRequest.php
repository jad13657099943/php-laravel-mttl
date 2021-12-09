<?php

namespace Modules\Coinv2\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetsLogListRequest extends FormRequest
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
            'symbol' => 'required|string',
            'page' => 'required|integer',
            'module' => 'string|nullable',
            'action' => 'string|nullable',
            'limit' => 'integer',
        ];
    }
}
