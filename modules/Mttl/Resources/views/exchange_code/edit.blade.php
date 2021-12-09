@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form method="post" class="layui-form">
                {{csrf_field()}}

                <div class="layui-form-item">
                    <label class="layui-form-label">总可领取次数</label>
                    <div class="layui-input-inline">
                        <input type="number" name="counts" value="{{$data->counts}}"
                               placeholder="总可领取次数（0为不限制）" required lay-verify="required|number" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">每人可领取的次数</label>
                    <div class="layui-input-inline">
                        <input type="number" name="quota" value="{{$data->quota}}"
                               placeholder="每人可领取的次数" required lay-verify="required|number" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">生效时间</label>
                    <div class="layui-input-inline">
                        <input type="text" name="effective_time" value="{{$data->effective_time}}"
                               placeholder="生效时间" required lay-verify="required" autocomplete="off"
                               class="layui-input" id="effective_time">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">失效时间</label>
                    <div class="layui-input-inline">
                        <input type="text" name="expiration_time" value="{{$data->expiration_time}}"
                               placeholder="失效时间" required lay-verify="required" autocomplete="off"
                               class="layui-input" id="expiration_time">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">是否启用</label>
                    <div class="layui-input-inline">
                        <input type="checkbox" name="state" @if($data->state == 1) checked @endif lay-skin="switch">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">
                        <input type="hidden" value="{{$data_id}}" name="data_id">
                        <button class="layui-btn" lay-submit lay-filter="add">立即提交</button>
                    </div>
                </div>


                <!--为了兼容时间控件无法选择问题-->
                <div style="height: 350px;">

                </div>

            </form>
        </div>
    </div>


@endsection

@push('after-scripts')
    <script>
        layui.use(['form', 'table', 'layedit', 'laydate'], function () {
            var $ = layui.$
                , layedit = layui.layedit
                , form = layui.form
                , table = layui.table
                , laydate = layui.laydate;
            laydate.render({
                elem: '#expiration_time'
                , type: 'datetime'
                ,value: '{{$data->expiration_time}}'
            });
            laydate.render({
                elem: '#effective_time'
                , type: 'datetime'
                ,value: '{{$data->effective_time}}'
            });

            form.on('submit(add)', function (data) {
                var url = '{{ route('m.mttl.api.admin.exchange_code.edit') }}';
                $.post(url, data.field, function (res) {
                    console.log(res);
                    layer.msg(res.msg, {icon: 1, time: 2000, shade: [0.8, '#393D49']}, function () {

                        window.parent.location.reload();
                    });
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
