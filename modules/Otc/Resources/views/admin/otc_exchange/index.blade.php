@extends('core::admin.layouts.app')

@section('content')
<div class="layui-card">
    <div class="layui-card-body">

        <div class="test-table-reload-btn" style="margin-bottom: 10px;">

            <div class="layui-inline">
                <input type="text" name="id" class="layui-input" id="id" placeholder=" 表ID ">
            </div>

            <div class="layui-inline">
                <input type="text" name="user_id" class="layui-input" id="user_id" placeholder=" 会员UID">
            </div>

            <div class="layui-inline">
                <input type="text" name="user_info" class="layui-input" id="user_info" placeholder=" 会员名|手机号 ">
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
                <select name="type" xm-select="type" id="type" lay-filter="type"
                        lay-verify="required" class="layui-select">
                    <option value="">--订单类型--</option>
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

    {{--<a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>--}}

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
            url: '{{ route('m.otc.api.admin.api.exchange.index') }}',
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

                /*{title:'操作', toolbar: '#table-useradmin-admin',width:100 }*/
                {field:'id', title: 'ID', width:120,}
                ,{field:'user_name', title: '会员', width:120,event:'show_user'}
                ,{field:'no', title: '订单号', width:200,}
                ,{field:'type_text', title: '订单类型', width:100,}
                ,{field:'status_text', title: '交易状态', width:120, }
                ,{field:'coin', title: '币种', width:100, }
                ,{field:'num', title: '买卖数量', width:100,}
                ,{field:'surplus', title: '剩余数量', width:100,}
                ,{field:'min', title: '单笔最小交易量', width:100, }
                ,{field:'max', title: '单笔最大交易量', width:100, }
                ,{field:'bank_list', title: '收款方式', width:120, }
                ,{field:'created_at', title: '创建时间',width:200,templet: function (res) {
                        return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                    } }
                ,{field:'updated_at', title: '更新时间',width:200,templet: function (res) {
                        return moment(res.updated_at).format("YYYY-MM-DD HH:mm:ss")
                    } }
                ,{field:'deleted_at', title: '删除时间' }
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
            if(e.event==='show_user'){
                var html = "<div style='padding-left: 10px'>" +
                    "<p>UID："+data.user.id+"</p>"+
                    "<p>用户名："+data.user.username+"</p>"+
                    "<p>手机号："+data.user.mobile+"</p>"+
                    "<p>邮箱："+data.user.email+"</p>"+
                    "<p>注册时间："+data.user.created_time+"</p>"+
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
                var user_info = $('#user_info').val();
                var user_id = $('#user_id').val();
                var coin = $('#coin').val();
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
                            user_info:user_info,
                            user_id:user_id,
                            coin:coin,
                            type:type,
                            status:status,
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


