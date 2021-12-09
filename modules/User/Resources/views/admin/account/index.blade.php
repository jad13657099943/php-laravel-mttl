@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <button type="button" class="layui-btn" data-type="create">添加账户</button>
            </div>

            <script type="text/html" id="toolColumn">

                <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-xs" lay-event="del">删除</a>

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
                url: '{{ route('m.user.api.admin.api.account.index') }}',
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
                id: "dataTable",
                cols: [[
                    {field: 'right', title: '操作', toolbar: '#toolColumn', width: 240},
                    {field: 'username', title: '账户名', width: 340},
                    {field: 'admin', title: '权限', width: 500},
                    {
                        field: 'created_at', title: '创建时间', width: 530, templet: function (res) {
                            return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                        }
                    }
                ]],
                text: {
                    none: '没有可用数据'
                },
            });
            function  ajax(url,data) {
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'post',
                    data:data,
                    success: function (data) {

                        window.location.reload();
                    },
                    error:function (data) {
                        layer.msg(data.responseJSON.message);
                    }
                });
            }
            table.on("tool(lay-table)", function (e) {
                var data = e.data;
                if (e.event === 'edit') {
                    if (data.id==1){
                        layer.msg('最高权限者，无法操作');
                    }else{
                        var urls = '{{ route('m.user.admin.account.edit') }}?id='+data.id;
                        layer.open({
                            type: 2
                            , title: "编辑账号"
                            , content: urls
                            , area: ['90%', '90%']
                        })
                    }

                }
                if (e.event === 'del') {
                    if(data.id==1){
                        layer.msg('最高权限者，无法操作');
                    }else{
                        var url='{{route('m.user.api.admin.api.account.del')}}'
                        var datas={id:data.id}
                        ajax(url,datas);
                    }
                }
            });

            let active = {
                reload: function () {
                    let user = $('#user');
                    let created_at = $('#created_at');
                    //执行重载
                    table.reload('dataTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        , where: {
                            user: user.val(),
                            created_at: created_at.val(),
                        }
                    }, 'data');
                },
                create: function () {
                    var url = '{{ route('m.user.admin.account.add') }}';
                    layer.open({
                        type: 2
                        , title: "添加账号"
                        , content: url
                        , area: ['90%', '90%']
                    })
                }
            };


            $('.layui-btn').on('click', function () {
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        })


    </script>
@endpush

