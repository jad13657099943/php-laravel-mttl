@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">


                <div class="layui-inline">
                    <input type="text" name="id" class="layui-input" id="id" placeholder=" 表ID ">
                </div>

                <div class="layui-inline">
                    <input type="text" name="from_user" class="layui-input" id="from_user" placeholder=" 转出会员ID|会员名 ">
                </div>

                <div class="layui-inline">
                    <input type="text" name="to_user" class="layui-input" id="to_user" placeholder=" 转入会员ID|会员名 ">
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
                    <input type="text" name="times" class="layui-input" id="laydate-range-datetime" style="width: 250px"
                           placeholder=" 搜索日期 ">
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>


        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">


        @{{# if(d.state == 2 ){ }}
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info_link">链上详情</a>
        @{{# } }}

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

            layui.use(['form', 'table', 'util', 'laydate', 'laytpl'], function () {

                var $ = layui.$
                    , util = layui.util
                    , form = layui.form
                    , table = layui.table
                    , laytpl = layui.laytpl
                    , laydate = layui.laydate;

                laydate.render({
                    elem: '#laydate-range-datetime'
                    , type: 'date'
                    , range: '||'
                });

                table.render({
                    elem: '#LAY-user-back-role',
                    toolbar: '#tableToolbar',
                    url: '{{ route('m.coin.api.admin.api.internal.index') }}',
                    method: 'post',
                    parseData: function (res) { //res 即为原始返回的数据
                        return {
                            'code': res.message ? 400 : 0, //解析接口状态
                            'msg': res.message || '加载失败', //解析提示文本
                            'count': res.total || 0, //解析数据长度
                            'data': res.data || [] //解析数据列表
                        };
                    },
                    cols: [[

                        {field: 'id', title: 'ID', width: 100, sort: true}
                        , {field: 'from_user_data', title: '转出会员', width: 150}
                        , {field: 'to_user_data', title: '转入会员', width: 150}
                        , {field: 'symbol', title: '币种',}
                        , {field: 'number', title: '转账数量', sort: true}
                        , {field: 'get_num', title: '实际获取', sort: true}
                        , {field: 'fee', title: '手续费', width: 100, sort: true}
                        , {
                            field: 'created_at', title: '时间', width: 250, sort: true, templet: function (res) {
                                return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                            }
                        }

                    ]],
                    text: {
                        none: '无相关数据'
                    },
                    page: true
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
                        var from_user = $('#from_user').val();
                        var to_user = $('#to_user').val();
                        var id = $('#id').val();
                        //执行重载
                        table.reload('LAY-user-back-role', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            , where: {
                                key: {
                                    times: times,
                                    symbol: symbol,
                                    from_user: from_user,
                                    to_user: to_user,
                                    id: id,
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

