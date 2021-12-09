@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <div class="layui-inline">
                    <input type="text" name="team_mark" class="layui-input" id="team_mark" placeholder=" 团队标识 ">
                </div>

                <div class="layui-inline">
                    <input type="text" name="balance" class="layui-input" id="balance" placeholder=" 数量大于 ">
                </div>


                <div class="layui-inline">
                    <input type="text" name="from_user" class="layui-input" id="user_info" placeholder=" 会员ID|钱包地址 ">
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

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <table class="layui-table">

                <thead>
                <tr>
                    <th>币种</th>
                    <th>持有人数</th>
                    <th>持币总量</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($total as $item)
                    <tr>
                        <td>{{$item->symbol}}</td>
                        <td>{{$item->total_count}}</td>
                        <td>{{ floatval( $item->total_num)}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>


        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">

        @{{# if(d.frozen == 0){ }}
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="frozen">冻结</a>
        @{{# } else { }}
        <a class="layui-btn layui-btn-xs" lay-event="relieve">解冻</a>
        @{{# } }}

    </script>
    <!--引入点击复制js-->
    <script src="/vendor/js/clipboard.min.js"></script>
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

                table.render({
                    elem: '#LAY-user-back-role',
                    toolbar: '#tableToolbar',
                    url: '{{ route('m.coin.api.admin.api.coin_asset.index') }}',
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

                        {title: '操作', toolbar: '#table-useradmin-admin', width: 100}
                        , {field: 'frozen_text', title: '冻结状态', width: 100, sort: true}
                        , {field: 'show', title: '团队标识', width: 100, sort: true}
                        , {
                            field: 'user_id', title: '会员ID', width: 200, sort: true, templet: function (res) {
                                return res.user.show_userid;
                            }
                        }
                        , {field: 'symbol', title: '币种', width: 200,}
                        , {field: 'balance', title: '数量', width: 200, sort: true}
                        , {
                            field: 'updated_at', title: '更新时间', width: 250, sort: true, templet: function (res) {
                                return moment(res.updated_at).format("YYYY-MM-DD HH:mm:ss")
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

                    //relieve

                });
                util.event('lay-event', events);


                var events = {

                    //解冻
                    relieve: function (obj_data) {
                        layer.confirm('确定要解除该记录？', function (index) {
                            i = ityzl_SHOW_LOAD_LAYER();
                            $.ajax({
                                type: 'post',
                                url: '{{ route('m.coin.api.admin.api.coin_asset.frozen') }}',
                                data: {"id": obj_data.id, frozen: 0},
                                dataType: 'json',
                                success: function (resp) {
                                    ityzl_CLOSE_LOAD_LAYER(i);
                                    layer.msg(resp.msg, {icon: 1, time: 2000, shade: [0.8, '#393D49']}, function () {

                                        window.location.reload();
                                    });
                                },
                                error: function (err) {
                                    ityzl_CLOSE_LOAD_LAYER(i);
                                    layer.msg('请求失败', {time: 2000});
                                }
                            });
                        });

                    },

                    //冻结
                    frozen: function (obj_data) {

                        layer.confirm('确定要冻结该记录？', function (index) {
                            i = ityzl_SHOW_LOAD_LAYER();
                            $.ajax({
                                type: 'post',
                                url: '{{ route('m.coin.api.admin.api.coin_asset.frozen') }}',
                                data: {"id": obj_data.id, frozen: 1},
                                dataType: 'json',
                                success: function (resp) {
                                    ityzl_CLOSE_LOAD_LAYER(i);
                                    layer.msg(resp.msg, {icon: 1, time: 2000, shade: [0.8, '#393D49']}, function () {

                                        window.location.reload();
                                    });
                                },
                                error: function (err) {
                                    ityzl_CLOSE_LOAD_LAYER(i);
                                    layer.msg('请求失败', {time: 2000});
                                }
                            });
                        });
                    }

                };


                //搜搜重载
                var $ = layui.$, active = {
                    reload: function () {

                        var times = $('#laydate-range-datetime').val();
                        var symbol = $('#symbol').val();
                        var user_info = $('#user_info').val();
                        var balance = $('#balance').val();
                        var id = $('#id').val();
                        var team_mark = $('#team_mark').val();
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
                                    team_mark: team_mark,
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


