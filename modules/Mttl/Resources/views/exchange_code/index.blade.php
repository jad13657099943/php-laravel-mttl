@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header" style="height:60px;display: flex;align-items: center;">
            <button class="layui-btn" onclick="create()">添加兑换码</button>
        </div>
        <div class="layui-card-body">

            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>


        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
    </script>

    @push('after-scripts')
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

                var tableIns = table.render({
                    elem: '#LAY-user-back-role',
                    toolbar: '#tableToolbar',
                    url: '{{ route('m.mttl.api.admin.exchange_code.index') }}',
                    method: 'get',
                    parseData: function (res) { //res 即为原始返回的数据
                        return {
                            'code': res.message ? 400 : 0, //解析接口状态
                            'msg': res.message || '加载失败', //解析提示文本
                            'count': res.total || 0, //解析数据长度
                            'data': res.data || [] //解析数据列表
                        };
                    },
                    cols: [[
                        {fixed: 'left', title: '操作', toolbar: '#table-useradmin-admin', width: 120}
                        , {field: 'id', title: '序号', width: 80}
                        , {field: 'code', title: '兑换码', width: 180}
                        , {field: 'amount', title: '金额', width: 120}
                        , {field: 'counts', title: '总可领取次数', width: 120}
                        , {field: 'quota', title: '每人可领取的次数', width: 150}
                        , {
                            field: 'effective_time', title: '生效时间', templet: function (res) {
                                return moment(res.effective_time).format("YYYY-MM-DD HH:mm:ss")
                            }
                        }
                        , {
                            field: 'expiration_time', title: '失效时间', templet: function (res) {
                                return moment(res.expiration_time).format("YYYY-MM-DD HH:mm:ss")
                            }
                        }
                        , {
                            field: 'state', title: '状态', width: 120, templet: function (res) {
                                return res.state === 1 ? '正常' : '禁用';
                            }
                        }
                        , {field: 'received_count', title: '已领取次数', width: 150}
                        , {field: 'received_users', title: '已领取人数', width: 150}
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

                    var data = e.data;

                    // 编辑
                    if (e.event === 'edit') {

                        let url = '{{ route('m.mttl.admin.exchange_code.edit') }}?id=' + data.id;
                        layer.open({
                            type: 2
                            , title: "编辑兑换码"
                            , content: url
                            , area: ['80%', '80%']
                        })

                    }

                });
                util.event('lay-event', events);


                var events = {};


                //搜搜重载
                var $ = layui.$, active = {
                    reload: function () {

                        var types = $('#types').val();

                        //执行重载
                        table.reload('LAY-user-back-role', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            , where: {
                                types: types
                            }
                        });
                    }
                };

                $('.test-table-reload-btn .layui-btn').on('click', function () {
                    var type = $(this).data('type');
                    active[type] ? active[type].call(this) : '';
                });

                window.create = function () {
                    var url = '{{ route('m.mttl.admin.exchange_code.create') }}';
                    layer.open({
                        type: 2
                        , title: "新增兑换码"
                        , content: url
                        , area: ['80%', '80%']
                    })
                };
            })
        </script>
    @endpush



