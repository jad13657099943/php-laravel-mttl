<?php


namespace Modules\User\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class PasswordRequest extends FormRequest
{
    public function rules()
    {
        return [
            'password' => ['required'],
            //  'invite_code' => ['required', 'string', 'max:20']
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
            'password.required' => trans('user::validation.密码必填'),
            // 'invite_code.required' => trans('user::validation.邀请码必填')
        ];
    }
}
