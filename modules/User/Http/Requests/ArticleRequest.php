<?php


namespace Modules\User\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'desc' => ['required', 'string'],
            'cate_id' => ['required', 'integer', 'min:0'],
            'sort' => ['required', 'integer', 'min:0'],
            'state' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages()
    {

        return [
            'title.required' => '标题必填',
            'content.required' => '详情内容必填',
            'desc.required' => '简述必填',
            'cate_id.required' => '分类必选',
            'sort.required' => '排序必填',
            'state.required' => '状态必选',
        ];
    }

}

