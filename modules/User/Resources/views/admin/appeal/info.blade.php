@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <table class="layui-table">
                <thead>
                <tr>
                    <th>详情：{{$info->message}}</th>
                </tr>
                <tr>
                    <th>时间：{{$info->created_at->format('Y-m-d H:i:s')}}</th>
                </tr>
                @if ($info->images)
                    <tr>
                        <th>
                            @foreach ($info->images as $item)
                                <img src="{{$item}}">
                            @endforeach
                        </th>
                    </tr>
                @endif
                </thead>
            </table>

            <form method="post"  class="layui-form" lay-filter="test1">
                {{csrf_field()}}


                <div class="layui-form-item">
                    <label class="layui-form-label">状态：</label>
                    <div class="layui-input-block">

                        @foreach ($state_list as $key=> $vo)
                            <input type="radio" name="state" value="{{ $key }}" title="{{ $vo }}" @if($info->state== $key)  checked @endif>
                        @endforeach

                    </div>
                </div>

                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">回复内容</label>
                    <div class="layui-input-block">
                        <textarea name="reply" placeholder="请输入内容" class="layui-textarea">{{$info->reply}}</textarea>
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">
                        <input type="hidden" name="id" value="{{ $info->id  }}">
                        <button class="layui-btn" lay-submit lay-filter="add">立即提交</button>
                    </div>
                </div>
            </form>


        </div>
    </div>
@endsection

@push('after-scripts')

    <script>
        layui.use(['form', 'table', 'util', 'laydate'], function () {

            var $ = layui.$
                , util = layui.util
                , form = layui.form
                , table = layui.table
                , laydate = layui.laydate;
            form.render(null, 'test1');

            form.on('submit(add)', function(data){

                var url = '{{ route('m.user.api.admin.api.appeal.edit_info') }}';
                $.post(url,data.field,function(res){

                    console.log(res);

                    //if(res.code==200){

                    layer.msg(res.msg,{icon: 1,time: 2000,shade: [0.8, '#393D49']},function(){

                        window.parent.location.reload();
                    });

                    /*}else{
                        layer.msg(res.msg, {time: 2000});
                    }*/
                },'json');
                return false;
            });
        })



    </script>
@endpush

