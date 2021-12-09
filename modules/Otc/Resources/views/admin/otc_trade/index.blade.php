@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <div class="layui-inline">
                    <input type="text" name="id" class="layui-input" id="id" placeholder=" 表ID ">
                </div>

                <div class="layui-inline">
                    <input type="text" name="buyer_id" class="layui-input" id="buyer_id" placeholder=" 买家UID|会员名">
                </div>

                <div class="layui-inline">
                    <input type="text" name="seller_id" class="layui-input" id="seller_id" placeholder=" 卖家UID|会员名 ">
                </div>

                <div class="layui-inline">
                    <select name="coin" xm-select="coin" id="coin" lay-filter="coin"
                            lay-verify="required" class="layui-select">
                        <option value="">--币种--</option>
                        @foreach ($coin as $key=> $vo)
                            <option value="{{ $vo }}">{{ $vo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-inline">
                    <select name="exchange_type" xm-select="exchange_type" id="exchange_type" lay-filter="exchange_type"
                            lay-verify="required" class="layui-select">
                        <option value="">--挂单类型--</option>
                        @foreach ($type as $key=> $vo)
                            <option value="{{ $key }}">{{ $vo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-inline">
                    <select name="type" xm-select="type" id="type" lay-filter="type"
                            lay-verify="required" class="layui-select">
                        <option value="">--撮合单类型--</option>
                        @foreach ($type as $key=> $vo)
                            <option value="{{ $key }}">{{ $vo }}</option>
                        @endforeach
                    </select>
                </div>


                <div class="layui-inline">
                    <select name="status" xm-select="status" id="status" lay-filter="status"
                            lay-verify="required" class="layui-select">
                        <option value="">--订单状态--</option>
                        @foreach ($status as $key=> $vo)
                            <option value="{{ $key }}">{{ $vo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-inline">
                    <input type="text" name="created_at" class="layui-input" id="laydate-range-datetime" style="width: 250px"
                           placeholder="创建时间">
                </div>
                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>

        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">
        <a class="layui-btn layui-btn-xs" lay-event="info">详情</a>
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
                    url: '{{ route('m.otc.api.admin.api.trade.index') }}',
                    method:'get',
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
                        ,{field:'id', title: 'ID', width:120,}
                        ,{field:'buyer_user_name', title: '买家会员', width:120,event:'show_buyer'}
                        ,{field:'seller_user_name', title: '卖家会员', width:120,event:'show_seller'}
                        ,{field:'exchange_type_text', title: '挂单类型', width:100,}
                        ,{field:'type_text', title: '撮合单类型', width:100,}
                        ,{field:'status_text', title: '交易状态', width:120, }
                        ,{field:'coin', title: '币种', width:100, }
                        ,{field:'num', title: '买卖数量', width:100,}
                        ,{field:'min', title: '单笔最小交易量', width:100, }
                        ,{field:'max', title: '单笔最大交易量', width:100, }
                        ,{field:'reason', title: '备注原因', width:100, }
                        ,{field:'created_at', title: '创建时间',width:200,templet: function (res) {
                                return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                            } }
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

                    var data = e.data;
                    if(e.event==='show_buyer'){
                        var html = "<div style='padding-left: 10px'>" +
                            "<p>UID："+data.buyer_user.user.id+"</p>"+
                            "<p>用户名："+data.buyer_user.user.username+"</p>"+
                            "<p>手机号："+data.buyer_user.user.mobile+"</p>"+
                            "<p>邮箱："+data.buyer_user.user.email+"</p>"+
                            "<p>注册时间："+data.buyer_user.user.created_time+"</p>"+
                            "</div>";
                        layer.open({
                            type: 1,
                            shade: 0.8,
                            offset: 'auto',
                            area: [500 + 'px',350+'px'], // area: [width + 'px',height+'px'] //原图显示
                            shadeClose:true,
                            scrollbar: false,
                            title: "记录ID"+data.id+"会员信息", //不显示标题
                            content: html, //捕获的元素，注意：最好该指定的元素要存放在body最外层，否则可能被其它的相对元素所影响
                        });
                    }
                    if(e.event==='show_seller'){
                        var html = "<div style='padding-left: 10px'>" +
                            "<p>UID："+data.seller_user.user.id+"</p>"+
                            "<p>用户名："+data.seller_user.user.username+"</p>"+
                            "<p>手机号："+data.seller_user.user.mobile+"</p>"+
                            "<p>邮箱："+data.seller_user.user.email+"</p>"+
                            "<p>注册时间："+data.seller_user.user.created_time+"</p>"+
                            "</div>";
                        layer.open({
                            type: 1,
                            shade: 0.8,
                            offset: 'auto',
                            area: [500 + 'px',350+'px'], // area: [width + 'px',height+'px'] //原图显示
                            shadeClose:true,
                            scrollbar: false,
                            title: "记录ID"+data.id+"会员信息", //不显示标题
                            content: html, //捕获的元素，注意：最好该指定的元素要存放在body最外层，否则可能被其它的相对元素所影响
                        });
                    }

                });
                util.event('lay-event', events);
                var events = {

                    info:function (obj_data) {
                        var url = '{{ route('m.otc.admin.otc.trade.info') }}?id='+obj_data.id;
                        layer.open({
                            type: 2
                            , title: "信息【"+obj_data.id+"】详情"
                            , content: url
                            , area: ['90%', '90%']
                        })
                    },
                };


                //搜搜重载
                var $ = layui.$, active = {
                    reload: function(){

                        var times = $('#laydate-range-datetime').val();
                        var seller_id = $('#seller_id').val();
                        var buyer_id = $('#buyer_id').val();
                        var coin = $('#coin').val();
                        var exchange_type = $('#exchange_type').val();
                        var type = $('#type').val();
                        var status = $('#status').val();
                        var id = $('#id').val();
                        //执行重载
                        table.reload('LAY-user-back-role', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            ,where: {
                                key: {
                                    times:times,
                                    seller_user:seller_id,
                                    buyer_user:buyer_id,
                                    coin:coin,
                                    type:type,
                                    status:status,
                                    id:id,
                                    exchange_type:exchange_type
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


