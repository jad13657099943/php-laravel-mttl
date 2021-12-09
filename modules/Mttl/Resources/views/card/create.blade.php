@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form method="post" class="layui-form">
                {{csrf_field()}}

{{--                <div class="layui-form-item">--}}
{{--                    <label class="layui-form-label">类型</label>--}}
{{--                    <div class="layui-input-inline">--}}
{{--                        <select name="types" xm-select="types" id="types" lay-filter="types"--}}
{{--                                lay-verify="required" class="layui-select">--}}
{{--                            <option value="">--类型--</option>--}}
{{--                            @foreach ($types as $key=>$vo)--}}
{{--                                <option value="{{ $key }}">{{ $vo }}</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                </div>--}}

                <div class="layui-form-item">
                    <label class="layui-form-label">本金</label>
                    <div class="layui-input-inline">
                        <input type="number" name="principal" value=""
                               placeholder="请输入本金" required lay-verify="required|number" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">可购买的级别</label>
                    <div class="layui-input-inline">
                        <input type="checkbox" name="buyable_level[0]" title="平民">
                        <input type="checkbox" name="buyable_level[1]" title="精神领袖">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">收益天数</label>
                    <div class="layui-input-inline">
                        <input type="number" name="total_days" value=""
                               placeholder="请输入收益天数" required lay-verify="required|number" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">每日收益率</label>
                    <div class="layui-input-inline">
                        <input type="number" name="daily_rate" value=""
                               placeholder="请输入每日收益率" required lay-verify="required|number" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">
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
                elem: '#start_data'
                , type: 'date'
            });

            form.on('submit(add)', function (data) {
                var url = '{{ route('m.mttl.api.admin.card.create') }}';
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
