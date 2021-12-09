@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form method="post" class="layui-form" lay-filter="form">
                {{csrf_field()}}


                <div class="layui-form-item">
                    <label class="layui-form-label">钱包备注</label>
                    <div class="layui-input-inline">
                        <input type="text" name="remark" value="{{$info->remark}}" required autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">钱包主链</label>
                    <div class="layui-input-inline">
                        <input type="text" name="chain"  value="{{$info->chain}}" disabled autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">使用类型</label>
                    <div class="layui-input-inline">
                        <input type="text"  name="type" value="{{$info->type}}" disabled autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">钱包类型</label>
                    <div class="layui-input-inline">
                        <input type="text" name="level" value="{{$info->level}}" disabled autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">钱包地址</label>
                    <div class="layui-input-inline">
                        <input type="text" name="address" value="{{$info->address}}"
                               required  placeholder="不是由TOKENIO生成的钱包必填此项，否则不用填" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">大于此值消息通知</label>
                    <div class="layui-input-inline">
                        <input type="text" name="notice_max" value="{{$info->notice_max}}"
                               required  placeholder="钱包余额大于此值时通知,0为不通知" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">小于此值消息通知</label>
                    <div class="layui-input-inline">
                        <input type="text" name="notice_min" value="{{$info->notice_min}}"
                               required  placeholder="钱包余额小于此值时通知,0为不通知" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">通知邮箱</label>
                    <div class="layui-input-inline">
                        <input type="text" name="notice" value="{{$info->notice}}"
                               required  placeholder="必填" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">
                        <input type="hidden" name="id" value="{{$info->id}}">
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
                , layedit = layui.layedit
                , form = layui.form
                , table = layui.table
                , laydate = layui.laydate;
            form.render(null, 'form');
            laydate.render({
                elem: '#start_time'
                ,type:'datetime'
            });
            laydate.render({
                elem: '#end_time'
                ,type:'datetime'
            });


            form.on('submit(add)', function(data){

                var url = '{{ route('m.coin.api.admin.api.system_wallet.edit_info') }}';
                $.post(url,data.field,function(res){

                    console.log(res);
                    layer.msg(res.msg,{icon: 1,time: 2000,shade: [0.8, '#393D49']},function(){

                        window.parent.location.reload();
                    });
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
