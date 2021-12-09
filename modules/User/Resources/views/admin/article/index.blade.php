@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="test-table-reload-btn" style="margin-bottom: 10px;">


                <div class="layui-inline">
                    <select name="state" xm-select="state" id="state" lay-verify="required" class="layui-select">
                        <option value="">--状态--</option>
                        @foreach ($state as $key=>$value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
                <button class="layui-btn" data-type="create">添加</button>
            </div>


            <script type="text/html" id="toolColumn">

                <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
                <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
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
                url: '{{ route('m.user.api.admin.api.article.index') }}',
                toolbar: '#bar-header-box',
                defaultToolbar: ['filter', {
                    title: '提示'
                    , layEvent: 'add'
                    , icon: 'layui-icon-addition'
                }],
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
                    {field: 'right', title: '操作', toolbar: '#toolColumn', width: 120},
                    {field: 'id', title: 'ID', width: 80},
                    {field: 'title', title: '标题'},
                    {field: 'sort', title: '排序', width: 100},
                    {field: 'cate_text', title: '分类', width: 120},
                    {field: 'state_text', title: '状态', width: 100},
                    {field: 'label_str', title: '标签', width: 120},
                    {
                        field: 'created_at', title: '时间', width: 170, templet: function (res) {
                            return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                        }
                    }
                ]],
                text: {
                    none: '没有可用数据'
                },
            });

            table.on('toolbar(lay-table)', function (obj) {
                var checkStatus = table.checkStatus(obj.config.id);
                switch (obj.event) {
                    case 'add':
                        var url = '{{ route('m.user.admin.article.create') }}';
                        location.href = url;
                        break;
                }
            });

            table.on("tool(lay-table)", function (obj) {
                // if (events[e.event]) {
                //     events[e.event].call(this, e.data);
                // }
                var data = obj.data;
                if (obj.event === 'del') {
                    layer.confirm('真的删除行么', function (index) {

                        var url = '{{ route('m.user.api.admin.api.article.del') }}?id=' + data.id;
                        $.post(url, data.field, function (res) {

                            layer.msg(res.msg, {icon: 1, time: 2000, shade: [0.8, '#393D49']}, function () {

                                obj.del();
                                layer.close(index);
                            });

                        }, 'json');
                    });
                } else if (obj.event === 'edit') {
                    location.href = '{{ route('m.user.admin.article.edit_info') }}?id=' + data.id;
                }
            });


            let active = {
                reload: function () {

                    //执行重载
                    table.reload('dataTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        , where: {
                            state: $("#state").val(),
                            symbol: $("#coin").val(),
                        }
                    }, 'data');
                },
                create:function (){
                    var url = '{{ route('m.user.admin.article.create') }}';
                    location.href = url;
                }
            };


            $('.layui-btn').on('click', function () {
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        })

    </script>
@endpush

