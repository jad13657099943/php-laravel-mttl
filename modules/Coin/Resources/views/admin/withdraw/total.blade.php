@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                {{--                <div class="layui-inline">--}}
                {{--                    <input type="text" name="user_info" class="layui-input" id="user_info" placeholder=" 充值会员ID|钱包地址 ">--}}
                {{--                </div>--}}
                <div class="layui-inline">
                    <input type="text" name="team_mark" class="layui-input" id="team_mark" placeholder=" 团队标识 ">
                </div>
                <div class="layui-inline">
                    <input type="text" name="times" class="layui-input" id="laydate-range-datetime" style="width: 250px"
                           placeholder=" 提现日期 ">
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>
            <div class="layui-input-inline">
                <input type="text" readonly="readonly" value="成功总计:{{$succeed}}" id="succeed" autocomplete="off" class="layui-input">
            </div>
            <div class="layui-input-inline">
                <input type="text" readonly="readonly" value="失败总计:{{$fail}}" id="fail" autocomplete="off" class="layui-input">
            </div>
            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>


        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">



    </script>

    @push('after-scripts')


        <script>

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
                    url: '{{ route('m.coin.api.admin.api.withdraw.total') }}',
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
                        {field: 'team_mark', title: '团队标识', width: 100, sort: false}
                        , {field: 'symbol', title: '提现币种', width: 150, sort: false}
                        , {field: 'success', title: '成功总金额', width: 150, sort: false}
                        , {field: 'fail', title: '失败总金额', width: 150, sort: false}
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
                        var team_mark = $('#team_mark').val();
                        var state = $('#state').val();
                        let URL='{{route('m.coin.api.admin.api.withdraw.sum')}}';
                        let DATA={ times: times, team_mark: team_mark, state: state};
                        ajax(URL,DATA);
                        //执行重载
                        table.reload('LAY-user-back-role', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            , where: {

                                    times: times,
                                    team_mark: team_mark,
                                    state: state

                            }
                        });
                    }
                };

                $('.test-table-reload-btn .layui-btn').on('click', function () {
                    var type = $(this).data('type');
                    active[type] ? active[type].call(this) : '';
                });

                function  ajax(url,data) {
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: 'post',
                        data:data,
                        success: function (data) {
                            $('#succeed').attr('value','成功总计:'+data.succeed);
                            $('#fail').attr('value','失败总计:'+data.fail);
                        },
                        error:function (data) {

                        }
                    });
                }

            })
        </script>
    @endpush

