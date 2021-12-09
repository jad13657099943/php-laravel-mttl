@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form method="post" class="layui-form" lay-filter="test1">
                {{csrf_field()}}

                @foreach($authority_list as $key => $vo)
                    <div class="layui-form-item">
                        <label class="layui-form-label">{{$vo}}</label>
                        <div class="layui-input-inline">
                            @if($key !== 'empty')
                                <input name="authority[{{$key}}]" type="checkbox"
                                       @if(isset($authority[$key]) && $authority[$key]) checked @endif
                                       @if(!isset($authority[$key])) checked @endif
                                       lay-skin="switch">
                            @endif
                            @if($key === 'empty')
                                <input name="authority[{{$key}}]" type="checkbox"
                                       @if(isset($authority[$key]) && $authority[$key]) checked @endif
                                       @if(!isset($authority[$key]))  @endif
                                       lay-skin="switch">
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">
                        <input type="hidden" name="user_id" value="{{ $info->user_id  }}">
                        <button class="layui-btn" lay-submit lay-filter="add">立即提交</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@push('after-scripts')
    <script>
        layui.use(['form', 'table', 'layedit', 'laydate'], function () {
            var $ = layui.$
                , form = layui.form;
            form.render(null, 'test1');


            form.on('submit(add)', function (data) {

                var url = '{{ route('m.user.api.admin.api.user.authority') }}';
                $.post(url, data.field, function (res) {

                    console.log(res);

                    //if(res.code==200){

                    layer.msg(res.msg, {icon: 1, time: 2000, shade: [0.8, '#393D49']}, function () {

                        window.parent.location.reload();
                    });

                    /*}else{
                        layer.msg(res.msg, {time: 2000});
                    }*/
                }, 'json');
                return false;
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
