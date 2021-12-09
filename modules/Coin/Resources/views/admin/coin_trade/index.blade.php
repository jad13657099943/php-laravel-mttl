@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <div class="layui-inline">
                    <input type="text" name="id" class="layui-input" id="id" placeholder=" 数据ID ">
                </div>


                <div class="layui-inline">
                    <input type="text" name="user_info" class="layui-input" id="user_info" placeholder=" 会员ID|会员名 ">
                </div>

                <div class="layui-inline">
                    <select name="symbol" xm-select="symbol" id="symbol" lay-filter="status" lay-verify="required" class="layui-select">
                        <option value="">--币种--</option>
                        @foreach ($coin as $vo)
                            <option value="{{ $vo->symbol }}">{{ $vo->symbol }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-inline">
                    <select name="state" xm-select="state" id="state" lay-filter="state" lay-verify="required" class="layui-select">
                        <option value="1000">--状态--</option>
                        @foreach ($state as $key=> $vo)
                            <option value="{{ $key }}">{{ $vo }}</option>
                        @endforeach
                    </select>
                </div>


                <div class="layui-inline">
                    <select name="action" xm-select="action" id="action" lay-filter="action" lay-verify="required" class="layui-select">
                        <option value="">--业务类型--</option>
                        @foreach ($module as $key=> $vo)
                            <option value="{{ $key }}">{{ $vo }}</option>
                        @endforeach
                    </select>
                </div>

                {{--<div class="layui-inline">
                    <input type="text" name="module_no" class="layui-input" id="module_no" placeholder=" 业务编号 ">
                </div>--}}

                <div class="layui-inline">
                    <input type="text" name="times" class="layui-input" id="laydate-range-datetime" style="width: 250px" placeholder=" 搜索日期 ">
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>


        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">

        {{--<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info_link">链上详情</a>--}}

        <a class="layui-btn layui-btn-xs" lay-event="info">详情</a>

        @{{# if(d.state == -4){ }}
        <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="trade_again">二次转账</a>
        @{{# } else if(d.state == -2 ) { }}
        <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="trade_man">人工转账</a>
        @{{# } else if(d.state == -1 ) { }}
        {{--<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="">请求转账</a>--}}
        @{{# } else if(d.state == 0 || d.state == 1 || d.state==2 ) { }}
        {{--<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info_link">链上详情</a>--}}
        @{{# } else { }}

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

            function ityzl_SHOW_LOAD_LAYER(){
                return layer.msg('处理中...', {icon: 16,shade: [0.5, '#b2b2b2'],scrollbar: false, time:0}) ;
            }
            function ityzl_CLOSE_LOAD_LAYER(index){
                layer.closeAll();
                layer.close(index);
            }

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
                    url: '{{ route('m.coin.api.admin.api.coin_trade.index') }}',
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

                        {title:'操作', toolbar: '#table-useradmin-admin',width:150 }
                        ,{field:'id', title: 'ID', width:80, sort: true}
                        ,{field:'user_info', title: '会员信息', width:250, sort: true}
                        ,{field:'num_text', title: '数量', width:120, sort: true}
                        ,{field:'gas', title: '手续费', width:120, sort: true}
                        ,{field:'state_text', title: '状态', width:170, sort: true}
                        ,{field:'from_text', title: '转出地址', width:150, sort: true,event:'copy_from'}
                        ,{field:'to_text', title: '转入地址', width:150, sort: true,event:'copy_to'}
                        ,{field:'hash_text', title: 'HASH', width:150, sort: true,event:'copy_hash'}
                        ,{field:'module_action', title: '业务类型', width:100, sort: true}
                        /*,{field:'no', title: '业务编号', width:100, sort: true}*/
                        ,{field:'created_at', title: '创建时间', width:200, sort: true, templet: function (res) {
                            return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                        }}
                        ,{field:'updated_at', title: '更新时间', width:200, sort: true, templet: function (res) {
                            return moment(res.updated_at).format("YYYY-MM-DD HH:mm:ss")
                        }}
                        ,{field:'state_info', title: '备注说明', width:200, sort: true}

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
                });
                util.event('lay-event', events);


                var btn=$('<button>');//新建按钮，并设置复制事件，最后触发按钮来执行事件
                var clipboard = new ClipboardJS(btn[0]);
                clipboard.on('success', function(e) {
                    console.log(e);
                    layer.msg((e.action=='copy'?'复制':'剪切')+'成功');
                });

                clipboard.on('error', function(e) {
                    layer.msg((e.action=='copy'?'复制':'剪切')+'失败');
                });


                var events = {

                    info:function(obj_data){

                        layer.open({
                            type: 2,
                            title: '数据详情',
                            shade: false,
                            maxmin: true,
                            area: ['80%', '90%'],
                            content: "{{ route('m.coin.admin.asset.coin_trade.info') }}?id="+obj_data.id
                        });
                    },

                    //外链详情
                    info_link:function(obj_data){

                        i = ityzl_SHOW_LOAD_LAYER();
                        $.ajax({
                            type: 'get',
                            url: '{{ route('m.coin.api.admin.api.coin_trade.link') }}',
                            data: {"id":obj_data.id},
                            dataType: 'json',
                            success: function (resp) {
                                ityzl_CLOSE_LOAD_LAYER(i);
                                if(resp.url==''){
                                    layer.msg('获取查询地址失败');
                                    return false;
                                }
                                window.open(resp.url); //打开新窗口
                            },
                            error:function (err) {
                                ityzl_CLOSE_LOAD_LAYER(i);
                                layer.msg('请求失败', {time: 2000});
                            }
                        });
                    },

                    trade_man:function(obj_data){
                        layer.open({
                            type: 2,
                            title: '手动转账',
                            shade: false,
                            maxmin: true,
                            area: ['80%', '90%'],
                            content: "{{ route('m.coin.admin.asset.coin_trade.info') }}?id="+obj_data.id
                        });
                    },


                    trade_again:function(obj_data){

                        layer.confirm('确定要二次请求转账？', function(index){
                            i = ityzl_SHOW_LOAD_LAYER();
                            $.ajax({
                                type: 'post',
                                url: "{{ route('m.coin.api.admin.api.coin_trade.again') }}",
                                data: {"id":obj_data.id},
                                dataType: 'json',
                                success: function (resp) {
                                    ityzl_CLOSE_LOAD_LAYER(i);
                                    layer.msg(resp.msg,{icon: 1,time: 2000,shade: [0.8, '#393D49']},function(){
                                        layer.close(index);
                                        window.location.reload();
                                    });
                                },
                                error:function (err) {
                                    ityzl_CLOSE_LOAD_LAYER(i);
                                    layer.msg('请求失败', {time: 2000});
                                }
                            });
                        });
                    },

                    //点击复制转出地址
                    copy_from: function (obj_data) {
                        /*clipboard.text=function(trigger) {
                            return obj_data.from;
                        };
                        btn.click();*/
                        if(obj_data.from_link!=''){
                            window.open(obj_data.from_link); //打开新窗口
                        }
                    },

                    //点击复制转入地址
                    copy_to: function (obj_data) {
                        /*clipboard.text=function(trigger) {
                            return obj_data.to;
                        };
                        btn.click();*/
                        if(obj_data.to_link!=''){
                            window.open(obj_data.to_link); //打开新窗口
                        }
                    },

                    //点击复制hash
                    copy_hash: function (obj_data) {
                        /*clipboard.text=function(trigger) {
                            return obj_data.hash;
                        };
                        btn.click();*/
                        if(obj_data.hash_link!=''){
                            window.open(obj_data.hash_link); //打开新窗口
                        }
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
                        var action = $("#action").val();
                        var module_no = $("#module_no").val();
                        var state = $("#state").val();
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
                                    action:action,
                                    module_no:module_no,
                                    state:state
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



