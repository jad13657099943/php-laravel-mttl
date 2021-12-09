@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header" style="height:60px;display: flex;align-items: center;">
            <button class="layui-btn" onclick="create()">添加能量卡</button>
        </div>
        <div class="layui-card-body">
{{--            <div class="test-table-reload-btn" style="margin-bottom: 10px;">--}}
{{--                <div class="layui-inline">--}}
{{--                    <select name="types" xm-select="types" id="types" lay-filter="types"--}}
{{--                            lay-verify="required" class="layui-select">--}}
{{--                        <option value="">--类型--</option>--}}
{{--                        @foreach ($types as $key=>$vo)--}}
{{--                            <option value="{{ $key }}">{{ $vo }}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}

{{--                <button class="layui-btn" data-type="reload">搜索</button>--}}
{{--            </div>--}}

            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>


        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>
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
                    url: '{{ route('m.mttl.api.admin.card.index') }}',
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
                        // , {field: 'type_text', title: '类型', width: 180}
                        , {field: 'principal', title: '金额', width: 120}
                        , {
                            field: 'buyable_level', title: '可购买的级别', width: 200, templet: function (res) {
                                let level = res.buyable_level;
                                let level_str = '';
                                level.forEach((item) => {
                                    level_str += item == 0 ? '平民 / ' : '精神领袖 / ';
                                });
                                return level_str;
                            }
                        }
                        , {field: 'total_days', title: '收益天数', width: 120}
                        , {
                            field: 'rate_of_return', title: '每日收益率', width: 150, templet: function (res) {
                                return res.rate_of_return + '%';
                            }
                        }
                        , {
                            field: 'total_revenue', title: '总收益金额', width: 150, templet: function (res) {
                                return res.total_revenue + ' U';
                            }
                        }
                        , {
                            field: 'enable', title: '状态', width: 150, templet: function (res) {
                                return res.enable === 1 ? '<span class="layui-badge layui-bg-green">启用</span>' :
                                    '<span class="layui-badge">关闭</span>';
                            }
                        }
                        , {
                            field: 'created_at', title: '创建时间', templet: function (res) {
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

                    var data = e.data;

                    // 删除
                    if (e.event === 'del') {

                        let url = '{{ route('m.mttl.api.admin.card.del') }}?id=' + data.id;

                        layer.confirm('确认删除？', {icon: 3, title: '提示'}, function (index) {
                            let i = ityzl_SHOW_LOAD_LAYER();
                            $.ajax({
                                type: 'post',
                                url: url,
                                dataType: 'json',
                                success: function (resp) {
                                    ityzl_CLOSE_LOAD_LAYER(i);
                                    tableIns.reload();
                                },
                                error: function (err) {
                                    ityzl_CLOSE_LOAD_LAYER(i);
                                    layer.msg('请求失败', {time: 2000});
                                }
                            });
                        });
                    }

                    // 编辑
                    if (e.event === 'edit') {

                        let url = '{{ route('m.mttl.admin.card.edit') }}?id=' + data.id;
                        layer.open({
                            type: 2
                            , title: "编辑能量卡"
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
                    var url = '{{ route('m.mttl.admin.card.create') }}';
                    layer.open({
                        type: 2
                        , title: "新增能量卡"
                        , content: url
                        , area: ['80%', '80%']
                    })
                };
            })
        </script>
    @endpush



