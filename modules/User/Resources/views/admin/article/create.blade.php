@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form method="post" class="layui-form"
                  action="{{$info->exists ? route('m.user.api.admin.api.article.edit_info',['id'=> $info['id']]) : route('m.user.api.admin.api.article.create')}}">
                {{csrf_field()}}
                <div class="layui-tab" lay-filter="locale">
                    <ul class="layui-tab-title">
                        @foreach (config('app.supported_locales') as $key => $locale)
                            <li @if($loop->first)class="layui-this"@endif>{{$locale['name']}}</li>
                        @endforeach
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-form-item">
                            <label class="layui-form-label">分类</label>
                            <div class="layui-input-inline">
                                <select name="cate_id" lay-verify="required">
                                    @foreach ($cate as $vo)
                                        <option value="{{ $vo->id }}"
                                                @if($info->cate_id== $vo->id ) selected @endif>{{ $vo->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-inline">
                                <input type="radio" name="state" value="1" title="上架"
                                       @if($info->state == 1)  checked @endif>
                                <input type="radio" name="state" value="0" title="下架"
                                       @if($info->state == 0)  checked @endif>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">标签</label>
                            <div class="layui-input-inline">
                                @foreach ($label as $vo)
                                    <input type="checkbox" name="label[]" value="{{$vo->id}}" title="{{$vo->name}}" @if($vo->is_check == 1 ) checked @endif>
                                @endforeach
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">排序</label>
                            <div class="layui-input-inline">
                                <input type="number" name="sort" required value="{{$info->sort}}"
                                       placeholder="数值越大越排前面" autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        @foreach (config('app.supported_locales') as $key => $locale)
                            <div
                                @if($loop->first)
                                class="layui-tab-item layui-show"
                                @else
                                class="layui-tab-item"
                                @endif>



                                <div class="layui-form-item">
                                    <label class="layui-form-label">标题</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="title[{{$key}}]"
                                               value="{{$info->getTranslation('title', $key) ?? ''}}"
                                               placeholder="请输入标题" autocomplete="off" class="layui-input">
                                    </div>
                                </div>



                                <div class="layui-form-item layui-form-text">
                                    <label class="layui-form-label">简述</label>
                                    <div class="layui-input-inline">
                                        <textarea name="desc[{{$key}}]" placeholder="请输入简述内容" class="layui-textarea">{{$info->getTranslation('desc', $key) ?? ''}}</textarea>
                                    </div>
                                </div>

                                <div class="layui-form-item upload" data-locale="{{$key}}">
                                    <label class="layui-form-label">封面图</label>
                                    <div class="layui-input-inline">
                                        <div class="layui-upload">
                                            <button type="button" class="layui-btn" id="covers_btn">
                                                上传封面图
                                            </button>
                                            <blockquote class="layui-elem-quote layui-quote-nm"
                                                        style="margin-top: 10px;">
                                                预览图：
                                                <div class="layui-upload-list" id="covers_image">
                                                    <div class="image-preview-box">
                                                        <img src="{{$info->getTranslation('image', $key) ?? ''}}"
                                                             class="layui-upload-img">
                                                        <input type="hidden" name="image[{{$key}}]"
                                                               value="{{$info->getTranslation('image', $key) ?? ''}}"/>
                                                        <button type="button"
                                                                class="layui-btn layui-btn-danger layui-btn-xs covers_remove">
                                                            删除
                                                        </button>
                                                    </div>
                                                </div>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">内容</label>
                                    <div class="layui-input-inline">
                                    <textarea type="text" name="content[{{$key}}]"
                                              placeholder="请输入公告内容" autocomplete="off" class="content layui-textarea">
                                        {{$info->getTranslation('content', $key) ?? ''}}
                                    </textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach



                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">
                        <button type="submit" class="layui-btn" lay-submit="" lay-filter="lay-announce">立即提交</button>

                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@push('after-scripts')
    <script>
        layui.use(['form', 'table', 'layedit', 'element', 'upload'], function () {
            var $ = layui.$
                , layedit = layui.layedit
                , form = layui.form
                , table = layui.table
                , upload = layui.upload
                , element = layui.element;
            form.render();
            $(document).on('click', '.covers_remove', function () {
                $(this).parent().remove();
            });
            $('.upload').each(function () {
                console.log(1);
                var $this = $(this);
                const $btn = $('#covers_btn', $this);
                const $images = $('#covers_image', $this);
                const locale = $this.data('locale');
                //多图片上传
                upload.render({
                    elem: $btn
                    , url: '{{route('admin.api.media.upload')}}'
                    , multiple: true
                    , done: function (res) {
                        if (res.message === undefined) {
                            //上传成功
                            $images.append('<div class="image-preview-box"><img src="' + res.url + '" class="layui-upload-img"><input type="hidden" name="image[' + locale + ']" value="' + res.url + '"/><button type="button" class="layui-btn layui-btn-danger layui-btn-xs covers_remove">删除</button></div>')
                        } else {
                            layer.msg('上传失败');
                        }
                    }
                });

            });

            $('textarea.content').each(function () {

                layedit.set({
                    uploadImage: {
                        url: '{{route('m.user.api.admin.api.upload.upload_for_layedit')}}'
                        , type: '' //默认post
                    }
                });

                layedit.build(this, {
                    height: 320,
                    tool: [
                        'strong' //加粗
                        , 'italic' //斜体
                        , 'underline' //下划线
                        , 'del' //删除线
                        , '|' //分割线
                        , 'left' //左对齐
                        , 'center' //居中对齐
                        , 'right' //右对齐
                        , 'link' //超链接
                        , 'unlink' //清除链接
                        , 'face' //表情
                        , 'image' //插入图片
                    ]
                })
            });
        })
    </script>
@endpush

<style>
    .layui-form-item .layui-input-inline {
        width: 800px !important;
    }

    .layui-form-label {
        box-sizing: initial;
        width: 200px !important;
    }
</style>
