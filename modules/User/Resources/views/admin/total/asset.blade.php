@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form method="post"  class="layui-form" lay-filter="test1">
                {{csrf_field()}}


                <div class="layui-form-item">
                    <label class="layui-form-label">会员ID</label>
                    <div class="layui-input-inline">
                        <input type="text" name="user_id" placeholder="请正确填写" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-inline">

                        <select name="coin" lay-verify="required">
                            @foreach ($coin as $key=> $vo)
                                <option value="{{ $vo }}" >{{ $vo }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">操作类型</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="state" value="1" title="增加" checked>
                        <input type="radio" name="state" value="0" title="减少">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">操作数量</label>
                    <div class="layui-input-inline">
                        <input type="text" name="number" value="" placeholder="请正确填写" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">
                        <button class="layui-btn" lay-submit lay-filter="add">立即提交</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@push('after-scripts')
    <script>
        layui.use(['form', 'table', 'layedit','laydate'], function () {
            var $ = layui.$
                , form = layui.form;
            form.render(null, 'test1');


            form.on('submit(add)', function(data){

                var url = '{{ route('m.user.api.admin.api.total.asset') }}';
                $.post(url,data.field,function(res){

                    console.log(res);

                    //if(res.code==200){

                    layer.msg(res.msg,{icon: 1,time: 2000,shade: [0.8, '#393D49']},function(){

                        window.location.reload();
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

<style>
    .layui-form-item .layui-input-inline {
        width: 800px !important;
    }

    .layui-form-label {
        box-sizing: initial;
        width: 200px !important;
    }
</style>
