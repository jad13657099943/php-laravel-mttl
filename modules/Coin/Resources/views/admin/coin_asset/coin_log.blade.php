@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <div class="layui-inline">
                    <input type="text" name="id" class="layui-input" id="id" placeholder=" 数据ID ">
                </div>


                <div class="layui-inline">
                    <input type="text" name="user_info" class="layui-input" id="user_info" placeholder=" 会员ID|钱包地址 ">
                </div>

                <div class="layui-inline">
                    <select name="symbol" xm-select="symbol" id="symbol" lay-filter="status" lay-verify="required"
                            class="layui-select">
                        <option value="">--币种--</option>
                        @foreach ($coin as $vo)
                            <option value="{{ $vo->symbol }}">{{ $vo->symbol }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-inline">
                    <select name="action" xm-select="action" id="action" lay-filter="action" lay-verify="required"
                            class="layui-select">
                        <option value="">--业务类型--</option>
                        @foreach ($action as $vo)
                            <option value="{{ $vo['action'] }}">{{ $vo['title']['zh_CN'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{--<div class="layui-inline">
                    <input type="text" name="module_no" class="layui-input" id="module_no" placeholder=" 业务编号 ">
                </div>--}}

                <div class="layui-inline">
                    <input type="text" name="times" class="layui-input" id="laydate-range-datetime" style="width: 250px"
                           placeholder=" 搜索日期 ">
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <div>
                总数：<span id="total_data"></span>
            </div>
            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>


        </div>
    </div>
@endsection
@push('after-scripts')
    <style>
        .layui-table-cell {
            height: auto !important;
        }
    </style>
    <script type="text/html" id="table-useradmin-admin">

        {{--<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info_link">链上详情</a>--}}

    </script>
    <!--引入点击复制js-->
    <script src="/vendor/js/clipboard.min.js"></script>
    @push('after-scripts')

        {{--<script type="text/html" id="tableToolbar">
            <div class="layui-btn-container">
                <button class="layui-btn layuiadmin-btn-role" lay-event="add">添加角色</button>
            </div>
        </script>--}}
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

                laydate.render({
                    elem: '#laydate-range-datetime'
                    , type: 'date'
                    , range: '||'
                });

                table.render({
                    elem: '#LAY-user-back-role',
                    toolbar: '#tableToolbar',
                    url: '{{ route('m.coin.api.admin.api.coin_asset.coin_log') }}',
                    method: 'post',
                    parseData: function (res) { //res 即为原始返回的数据
                        return {
                            'code': res.message ? 400 : 0, //解析接口状态
                            'msg': res.message || '加载失败', //解析提示文本
                            'count': res.total || 0, //解析数据长度
                            'data': res.data || [], //解析数据列表
                            'total_data': res.total_data,
                        };
                    },
                    cols: [[

                        {field: 'id', title: 'ID', width: 100, sort: true,}
                        , {
                            field: 'user_id', title: '会员ID', width: 100, sort: true, templet: function (res) {
                                return res.user.show_userid
                            }
                        }
                        , {
                            field: 'username', title: '钱包地址', width: 150, sort: true, templet: function (res) {
                                return res.user.address;
                            }
                        }
                        , {field: 'num_text', title: '数量', width: 150, sort: true}
                        /*,{field:'module_action', title: '业务动作', width:150, sort: true}*/
                        /*,{field:'no', title: '业务ID', width:100, sort: true}*/
                        , {
                            field: 'created_at', title: '时间', width: 200, templet: function (res) {
                                return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                            }
                        }
                        , {field: 'info', title: '描述', minWidth: 150}
                        , {
                            field: 'extra', title: '详细', templet: function (res) {
                                let msg = "";
                                if (res.reward) {
                                    msg += "来源用户：" + res.reward.source_show_userid + ""
                                    // msg += "代数：" + res.reward.algebra + ""
                                    if (res.reward.extra && res.reward.extra.card_amount) {
                                        msg += "<br/>能量卡面额：" + res.reward.extra.card_amount + ""
                                    }
                                }
                                return msg;
                            }
                        }

                    ]],
                    text: {
                        none: '无相关数据'
                    },
                    page: true,
                    done: function (res, curr, count) {
                        $("#total_data").html(res.total_data);
                    }
                });
                table.on("tool(LAY-user-back-role)", function (e) {
                    if (events[e.event]) {
                        events[e.event].call(this, e.data);
                    }
                });
                util.event('lay-event', events);


                var events = {};


                //搜搜重载
                var $ = layui.$, active = {
                    reload: function () {

                        var times = $('#laydate-range-datetime').val();
                        var symbol = $('#symbol').val();
                        var user_info = $('#user_info').val();
                        var balance = $('#balance').val();
                        var id = $('#id').val();
                        var action = $("#action").val();
                        var module_no = $("#module_no").val();
                        //执行重载
                        table.reload('LAY-user-back-role', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            , where: {
                                key: {
                                    times: times,
                                    symbol: symbol,
                                    user_info: user_info,
                                    balance: balance,
                                    id: id,
                                    action: action,
                                    module_no: module_no
                                }
                            }
                        });
                    }
                };

                $('.test-table-reload-btn .layui-btn').on('click', function () {
                    var type = $(this).data('type');
                    active[type] ? active[type].call(this) : '';
                });

            })
        </script>
    @endpush



