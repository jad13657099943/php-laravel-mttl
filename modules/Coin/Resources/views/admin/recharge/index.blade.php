@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <div class="layui-inline">
                    <input type="text" name="team_mark" class="layui-input" id="team_mark" placeholder=" 团队标识 ">
                </div>
                <div class="layui-inline">
                    <input type="text" name="id" class="layui-input" id="id" placeholder=" 表ID ">
                </div>
                <div class="layui-inline">
                    <input type="text" name="user_info" class="layui-input" id="user_info" placeholder=" 转账会员ID|会员名 ">
                </div>

                <div class="layui-inline">
                    <input type="text" name="to" class="layui-input" id="to" placeholder=" 转入地址 ">
                </div>


                <div class="layui-inline">
                    <select name="state" xm-select="order_status" id="state" lay-filter="status" lay-verify="required"
                            class="layui-select">
                        <option value="1000">--状态类型--</option>
                        @foreach ($state as $key=> $vo)
                            <option value="{{ $key }}">{{ $vo }}</option>
                        @endforeach

                    </select>
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


        {{--@{{# if(d.state == 2 ){ }}
            <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info_link">链上详情</a>
        @{{# } }}--}}

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
                    url: '{{ route('m.coin.api.admin.api.recharge.index') }}',
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

                        /*{title:'操作', width:120,toolbar: '#table-useradmin-admin' }*/
                        {field: 'id', title: 'ID', width: 100, sort: true},

                        {field: 'show', title: '团队标识', width: 100, sort: true}
                        , {
                            field: 'user_id', title: '会员ID', width: 100, sort: true, templet: function (res) {
                                return res.project_user.show_userid
                            }
                        }
                        , {field: 'username', title: '会员名', width: 150, sort: true}
                        , {field: 'value_text', title: '数量', width: 150, sort: true}
                        , {field: 'state_text', title: '状态', width: 150, sort: true}
                        , {field: 'from_text', title: '转出地址', event: 'copy_from'}
                        , {field: 'to_text', title: '转入地址', event: 'copy_to'}
                        , {field: 'hash_text', title: 'HASH', event: 'copy_hash'}
                        , {
                            field: 'created_at', title: '创建时间', width: 250, sort: true, templet: function (res) {
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


                var btn = $('<button>');//新建按钮，并设置复制事件，最后触发按钮来执行事件
                var clipboard = new ClipboardJS(btn[0]);
                clipboard.on('success', function (e) {
                    console.log(e);
                    layer.msg((e.action == 'copy' ? '复制' : '剪切') + '成功');
                });

                clipboard.on('error', function (e) {
                    layer.msg((e.action == 'copy' ? '复制' : '剪切') + '失败');
                });


                var events = {

                    //设置为已转账
                    info_link: function (obj_data) {


                        //以下方法为完成功能
                        i = ityzl_SHOW_LOAD_LAYER();
                        $.ajax({
                            type: 'get',
                            url: '{{ route('m.coin.api.admin.api.recharge.link') }}',
                            data: {"id": obj_data.id},
                            dataType: 'json',
                            success: function (resp) {
                                console.log(resp);
                                ityzl_CLOSE_LOAD_LAYER(i);
                                if (resp.url == '') {
                                    layer.msg('获取查询地址失败');
                                    return false;
                                }
                                window.open(resp.url); //打开新窗口
                            },
                            error: function (err) {
                                ityzl_CLOSE_LOAD_LAYER(i);
                                layer.msg('请求失败', {time: 2000});
                            }
                        });
                    },


                    //点击复制转出地址
                    copy_from: function (obj_data) {
                        /*clipboard.text=function(trigger) {
                            return obj_data.from;
                        };
                        btn.click();*/
                        if (obj_data.from_link != '') {
                            window.open(obj_data.from_link); //打开新窗口
                        }
                    },

                    //点击复制转入地址
                    copy_to: function (obj_data) {
                        /*clipboard.text=function(trigger) {
                            return obj_data.to;
                        };
                        btn.click();*/
                        if (obj_data.to_link != '') {
                            window.open(obj_data.to_link); //打开新窗口
                        }

                    },

                    //点击复制hash
                    copy_hash: function (obj_data) {
                        /*clipboard.text=function(trigger) {
                            return obj_data.hash;
                        };
                        btn.click();*/
                        if (obj_data.hash_link != '') {
                            window.open(obj_data.hash_link); //打开新窗口
                        }
                    },

                };


                //搜搜重载
                var $ = layui.$, active = {
                    reload: function () {

                        var times = $('#laydate-range-datetime').val();
                        var symbol = $('#symbol').val();
                        var from = $('#from').val();
                        var to = $('#to').val();
                        var hash = $('#hash').val();
                        var user_info = $('#user_info').val();
                        var id = $('#id').val();
                        var state = $('#state').val();
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
                                    from: from,
                                    to: to,
                                    user_info: user_info,
                                    id: id,
                                    state: state,
                                    hash: hash,
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

