@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="test-table-reload-btn" style="margin-bottom: 10px;">
                <div class="layui-inline">
                    <select name="state" xm-select="state" id="state" lay-filter="state"
                            lay-verify="required" class="layui-select">
                        <option value="">--状态--</option>
                        <option value="1">生效中</option>
                        <option value="2">已过期</option>
                    </select>
                </div>
                <div class="layui-inline">
                    <input type="text" name="user_id" class="layui-input" id="user_id" placeholder="用户ID">
                </div>
                <div class="layui-inline">
                    <input type="text" name="team_mark" class="layui-input" id="team_mark" placeholder="团队标识">
                </div>
                <div class="layui-inline">
                    <input type="text" name="created_at" class="layui-input" id="created_at" style="width: 250px"
                           placeholder="购买时间">
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

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
                    elem: '#created_at'
                    , type: 'date'
                    , range: '||'
                });

                var tableIns = table.render({
                    elem: '#LAY-user-back-role',
                    toolbar: '#tableToolbar',
                    url: '{{ route('m.mttl.api.admin.card_buy.index') }}',
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
                        // {fixed: 'left', title: '操作', toolbar: '#table-useradmin-admin', width: 120}
                        {field: 'id', title: '序号', width: 80}
                        , {
                            field: 'show_userid', title: '用户ID', width: 100, templet: function (res) {
                                return res.user.show_userid;
                            }
                        }
                        // , {field: 'type_text', title: '能量卡类型', width: 180}
                        , {field: 'principal', title: '能量卡金额', width: 120}
                        , {field: 'total_days', title: '收益天数', width: 120}
                        , {
                            field: 'rate_of_return', title: '每日收益率', width: 100, templet: function (res) {
                                return res.rate_of_return + '%';
                            }
                        }
                        , {field: 'issued_days', title: '已发放天数', width: 120}
                        , {field: 'surplus_days', title: '剩余天数', width: 120}
                        , {field: 'issued_amount', title: '已发放奖金', width: 120}
                        , {
                            field: 'automatic', title: '购买类型', width: 120, templet: function (res) {
                                return res.automatic == 1 ? "<span class=\"layui-badge layui-bg-green\">自动购买</span>" :
                                    '<span class="layui-badge layui-bg-blue">手动购买</span>';
                            }
                        }
                        , {
                            field: 'finish_date', title: '结束时间', width: 120, templet: function (res) {
                                return moment(res.finish_date).format("YYYY-MM-DD")
                            }
                        }
                        , {
                            field: 'created_at', title: '购买时间', templet: function (res) {
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

                });
                util.event('lay-event', events);


                var events = {};


                //搜搜重载
                var $ = layui.$, active = {
                    reload: function () {

                        let user_id = $('#user_id').val();
                        //let types = $('#types').val();
                        let created_at = $('#created_at').val();
                        let state = $('#state').val();
                        let team_mark = $('#team_mark').val();

                        //执行重载
                        table.reload('LAY-user-back-role', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            , where: {
                                // types: 1,
                                user_id: user_id,
                                created_at: created_at,
                                state: state,
                                team_mark: team_mark
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



