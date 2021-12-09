<?php


namespace Modules\User\Http\Requests;


class NoneRequest
{
    public function rules()
    {
        return [
            'address' => ['required','string', 'max:50'],
            'none' => ['required'],
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
            'address.required' => trans('user::validation.钱包地址必填'),
            'none.required' => trans('user::validation.密码必填'),
            // 'invite_code.required' => trans('user::validation.邀请码必填')
        ];
    }
}
