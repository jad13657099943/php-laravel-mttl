@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form method="post" class="layui-form" lay-filter="form">
                {{csrf_field()}}


                <div class="layui-form-item">
                    <label class="layui-form-label">钱包备注</label>
                    <div class="layui-input-inline">
                        <input type="text" name="remark" value=""
                               required  placeholder="如：归集钱包、补gas钱包、支出钱包等" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">钱包主链</label>
                    <div class="layui-input-inline">
                        <select name="chain" xm-select="chain" id="chain" lay-filter="chain" lay-verify="required" class="layui-select">
                            @foreach ($chain as $key=> $vo)
                                <option value="{{ $key }}">{{ $vo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">使用类型</label>
                    <div class="layui-input-inline">
                        <select name="type" xm-select="type" id="type" lay-filter="type" lay-verify="required" class="layui-select">
                            @foreach ($type as $key=> $vo)
                                <option value="{{ $key }}">{{ $vo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">钱包类型</label>
                    <div class="layui-input-inline">
                        <select name="level" xm-select="level" id="level" lay-filter="level" lay-verify="required" class="layui-select">
                            @foreach ($level as $key=> $vo)
                                <option value="{{ $key }}">{{ $vo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">由TOKENIO生成</label>
                    <div class="layui-input-block">
                        <input type="radio" name="is_tokenio" value="1" title="是" checked>
                        <input type="radio" name="is_tokenio" value="0" title="否" >
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">TOKENIO版本</label>
                    <div class="layui-input-block">
                        <input type="radio" name="tokenio_version" value="1" title="V1" checked>
                        <input type="radio" name="tokenio_version" value="2" title="V2" >
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">钱包地址</label>
                    <div class="layui-input-inline">
                        <input type="text" name="address" value=""
                               required  placeholder="不是由TOKENIO生成的钱包必填此项，否则不用填" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item" style="display: none">
                    <label class="layui-form-label">大于此值消息通知</label>
                    <div class="layui-input-inline">
                        <input type="text" name="notice_max" value="0"
                               required  placeholder="钱包余额大于此值时通知,0为不通知" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item" style="display: none">
                    <label class="layui-form-label">小于此值消息通知</label>
                    <div class="layui-input-inline">
                        <input type="text" name="notice_min" value="0"
                               required  placeholder="钱包余额小于此值时通知,0为不通知" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item" style="display: none">
                    <label class="layui-form-label">通知邮箱</label>
                    <div class="layui-input-inline">
                        <input type="text" name="notice" value="test@163.com"
                               required  placeholder="必填" autocomplete="off" class="layui-input">
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

                var url = '{{ route('m.coin.api.admin.api.system_wallet.create') }}';
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
