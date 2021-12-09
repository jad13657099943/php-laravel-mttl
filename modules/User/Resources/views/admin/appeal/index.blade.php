@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">
                <div class="layui-inline">
                    <input type="text" name="user" class="layui-input" id="user" placeholder="会员UID">
                </div>

                <div class="layui-inline">
                    <select name="state" xm-select="state" id="state" lay-verify="required" class="layui-select">
                        <option value="">--处理状态--</option>
                        @foreach ($state_list as $key=>$grand)
                            <option value="{{ $key }}">{{ $grand }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <script type="text/html" id="toolColumn">

                <a class="layui-btn layui-btn-xs" lay-event="info">详情 & 处理</a>
            </script>
            <table id="lay-table" lay-filter="lay-table"></table>
        </div>
    </div>
@endsection

@push('after-scripts')

    <script>
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

            table.render({
                elem: '#lay-table',
                url: '{{ route('m.user.api.admin.api.appeal.index') }}',
                parseData: function (res) { //res 即为原始返回的数据
                    return {
                        'code': res.message ? 400 : 0, //解析接口状态
                        'msg': res.message || '加载失败', //解析提示文本
                        'count': res.total, //解析数据长度
                        'data': res.data || [] //解析数据列表
                    };
                },
                page: {
                    layout: ['count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                },
                id:"dataTable",
                cols: [[
                    {field: 'right',title: '操作',toolbar: '#toolColumn',width:140},
                    {field: 'id',title: 'ID',width:100},
                    {field: 'user_info',title: '申诉会员',width:180},
                    {field: 'state_text',title: '状态',width:180},
                    {field: 'type_text',title: '类型',width:120},
                    {field: 'created_at',title: '时间',width:170,templet: function (res) {
                            return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")}},
                    {field: 'message',title: '内容'},
                ]],
                text: {
                    none: '没有可用数据'
                },
            });
            table.on("tool(lay-table)", function (e) {
                // if (events[e.event]) {
                //     events[e.event].call(this, e.data);
                // }
                var data = e.data;
                if(e.event ==='info'){

                    var url = '{{ route('m.user.admin.appeal.info') }}?id='+data.id;

                    layer.open({
                        type: 2
                        , title: '查看申诉：' + data['id'] + "信息"
                        , content: url
                        , area: ['90%', '90%']
                    })
                }
            });



            let active = {
                reload: function(){
                    let state = $('#state');
                    let  user =$('#user');
                    //执行重载
                    table.reload('dataTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        ,where: {
                            show:user.val(),
                            state: state.val(),
                        }
                    }, 'data');
                }
            };


            $('.layui-btn').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        })



    </script>
@endpush

