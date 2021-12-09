<?php

namespace Modules\Mttl\Http\Requests\Api\Reward;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Mttl\Models\RewardLog;

class DetailRequest extends FormRequest
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
                'string', Rule::in([
                    RewardLog::TYPE_ASSEMBLY,
                    RewardLog::TYPE_LEVEL,
                    RewardLog::TYPE_PEER,
                    RewardLog::TYPE_RECOMMEND,
                    RewardLog::TYPE_INCREASE,
                    'today'
                ]), 'required'
            ],
            'date' => [
                'date_format:m-d', 'nullable'
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
