@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            {{--<div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <div class="layui-inline">
                    <input type="text" name="balance" class="layui-input" id="balance" placeholder=" 数量大于 ">
                </div>

                <div class="layui-inline">
                    <input type="text" name="from_user" class="layui-input" id="user_info" placeholder=" 会员ID|会员名 ">
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>--}}

            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>

        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">

        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>

    </script>

    @push('after-scripts')

        <script>
            layui.use(['form', 'table', 'util','laydate'], function () {

                var $ = layui.$
                    , util = layui.util
                    , form = layui.form
                    , table = layui.table
                    , laydate = layui.laydate;

                laydate.render({
                    elem: '#laydate-range-datetime'
                    ,type: 'date'
                    ,range: '||'
                });

                table.render({
                    elem: '#LAY-user-back-role',
                    toolbar: '#tableToolbar',
                    url: '{{ route('m.coin.api.admin.api.coin.index') }}',
                    method:'post',
                    parseData: function (res) { //res 即为原始返回的数据
                        return {
                            'code': res.message ? 400 : 0, //解析接口状态
                            'msg':res.message || '加载失败', //解析提示文本
                            'count': res.total || 0, //解析数据长度
                            'data': res.data || [] //解析数据列表
                        };
                    },
                    cols: [[

                        {title:'操作', toolbar: '#table-useradmin-admin',width:100 }
                        ,{field:'chain', title: '主链', width:100, sort: true}
                        ,{field:'symbol', title: '币种', width:200, sort: true}
                        ,{field:'withdraw_min', title: '提币最小额度', width:200,}
                        ,{field:'withdraw_max', title: '提币最大额度', width:200,}
                        ,{field:'withdraw_fee', title: '提币手续费', width:200, sort: true}
                        ,{field:'withdraw_state_text', title: '提现状态', width:200, sort: true}
                        ,{field:'gas_price', title: '旷工费价格', width:200, sort: true}
                        ,{field:'status_text', title: '币种状态', width:200, sort: true}
                    ]],
                    text: {
                        none: '无相关数据'
                    },
                    page: true
                });
                table.on("tool(LAY-user-back-role)", function(e) {
                    if (events[e.event]) {
                        events[e.event].call(this, e.data);
                    }

                    //relieve

                });
                util.event('lay-event', events);
                var events = {

                    edit:function (obj_data) {
                        var url = '{{ route('m.coin.admin.asset.coin.edit') }}?id='+obj_data.id;
                        layer.open({
                            type: 2
                            , title: "编辑【"+obj_data.symbol+"】"
                            , content: url
                            , area: ['90%', '90%']
                        })
                    },
                };




                //搜搜重载
                var $ = layui.$, active = {
                    reload: function(){

                        var times = $('#laydate-range-datetime').val();
                        var symbol = $('#symbol').val();
                        var user_info = $('#user_info').val();
                        var balance = $('#balance').val();
                        var id = $('#id').val();
                        //执行重载
                        table.reload('LAY-user-back-role', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            ,where: {
                                key: {
                                    times:times,
                                    symbol:symbol,
                                    user_info:user_info,
                                    balance:balance,
                                    id:id,
                                }
                            }
                        });
                    }
                };

                $('.test-table-reload-btn .layui-btn').on('click', function(){
                    var type = $(this).data('type');
                    active[type] ? active[type].call(this) : '';
                });

            })
        </script>
    @endpush


