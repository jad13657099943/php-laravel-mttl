<?php


namespace Modules\User\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
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
            'username' => [
                Rule::requiredIf(function() {
                    return empty($this->email) && empty($this->mobile);
                }),
                'nullable',
                'string',
                Rule::unique(User::table())
            ],
            'password' => ['string', 'min:8', 'max:30'],
            'email' => [
                'nullable',
                'email',
                Rule::unique(User::table())
            ],
            'mobile' => [
                'nullable',
                Rule::phone()->country(config('core::register.mobile.countries', ['CN'])),
                //Rule::unique(User::table())
            ],
            'code' => ['required_with:mobile']
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'mobile' => 'The :attribute field contains an invalid number.',
        ];
    }
}
