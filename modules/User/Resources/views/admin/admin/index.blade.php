@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <button type="button" class="layui-btn" data-type="create">添加角色</button>
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
                url: '{{ route('m.user.api.admin.api.admin.index') }}',
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
                    {field: 'right', title: '操作', toolbar: '#toolColumn', width: 160},
                    {field: 'name', title: '角色名', width: 250},
                    {field: 'menus', title: '权限', width: 550},
                    {
                        field: 'created_at', title: '创建时间', width: 450, templet: function (res) {
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
                    var urls = '{{ route('m.user.admin.admin.edit') }}?id='+data.id;
                    layer.open({
                        type: 2
                        , title: "编辑角色"
                        , content: urls
                        , area: ['90%', '90%']
                    })
                }
                if (e.event === 'del') {
                    layer.open({
                        content: '删除该角色会导致,该角色账户权限失效!是否确定?'
                        ,btn: ['确定', '取消']
                        ,yes: function(index, layero){
                            var url='{{route('m.user.api.admin.api.admin.del')}}'
                            var datas={id:data.id}
                            ajax(url,datas);
                        }
                        ,btn2: function(index, layero){
                            //按钮【按钮二】的回调

                            //return false 开启该代码可禁止点击该按钮关闭
                        }
                        ,cancel: function(){
                            //右上角关闭回调

                            //return false 开启该代码可禁止点击该按钮关闭
                        }
                    });
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
                    var url = '{{ route('m.user.admin.admin.add') }}';
                    layer.open({
                        type: 2
                        , title: "添加角色"
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

