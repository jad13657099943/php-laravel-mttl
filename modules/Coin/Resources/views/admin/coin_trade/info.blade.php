@extends('core::admin.layouts.app')
@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">数据详情</div>
                    <div class="layui-card-body">
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th colspan="3">订单数据</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>信息ID：{{$info->id}}</td>
                                <td>会员ID：{{$info->user_id}}</td>
                                <td>信息状态：{{$info->state_text}}</td>
                            </tr>

                            <tr>
                                <td>转出数量：{{$info->num}} {{ $info->symbol  }} </td>
                                <td>主链信息：{{$info->coin->chain}}</td>
                                <td>{{$msg}}</td>
                            </tr>

                            <tr>
                                <td>转出地址：{{$info->from}} </td>
                                <td>转入地址：{{$info->to}}</td>
                                <td>交易hash：{{$info->hash}}</td>
                            </tr>
                            <tr>
                                <td>创建时间：{{$info->created_at}}</td>
                                <td>更新时间：{{$info->updated_at}}</td>
                                <td>备注：{{$info->state_info}}</td>
                            </tr>
                            </tbody>
                        </table>


                        @if($qrcode!='')

                            <form class="layui-form" method="post" lay-filter="form">
                                {{csrf_field()}}
                                <div class="layui-form-item">

                                    <div id="qrcode" style="margin: 30px;"></div>
                                    <div style="color: red">建议使用imToken钱包，选择对应的币种进行扫码转账，请仔细核对转账信息是否完整无误</div>
                                    <div class="layui-input-inline" style="width: 500px !important;">
                                        <input type="radio" name="res" lay-skin="primary" value="0" checked
                                               title="未手动转账">
                                        <input type="radio" name="res" lay-skin="primary" value="1" title="已手动转账">
                                    </div>
                                </div>


                                <div class="layui-form-item">
                                    <label class="layui-form-label">
                                    </label>
                                    <input type="hidden" name="id" value="{{$info->id}}"/>
                                    <button class="layui-btn" lay-submit lay-filter="add">立即提交</button>
                                </div>

                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('after-scripts')
    <script src="/vendor/js/jquery.min.js"></script>
    <script src="/vendor/js/jquery.qrcode.min.js"></script>
@endpush

@push('after-scripts')
    <script>
        $(document).ready(function (){

            jQuery('#qrcode').qrcode({
                render: "canvas", //也可以替换为table
                width: 150,
                height: 150,
                text: "{{$qrcode}}"
            });
        });

    </script>
    <script>

        function ityzl_SHOW_LOAD_LAYER() {
            return layer.msg('处理中...', {icon: 16, shade: [0.5, '#b2b2b2'], scrollbar: false, time: 0});
        }

        function ityzl_CLOSE_LOAD_LAYER(index) {
            layer.closeAll();
            layer.close(index);
        }

        layui.use(['form', 'table', 'util', 'laydate'], function () {

            var $ = layui.$
                , util = layui.util
                , form = layui.form
                , table = layui.table
                , laydate = layui.laydate;
            form.render(null, 'form');

            form.on('submit(add)', function (data) {


                var url = '{{ route('m.coin.api.admin.api.coin_trade.manual') }}';
                console.log(url);
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



