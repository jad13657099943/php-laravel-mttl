@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <div class="test-table-reload-btn" style="margin-bottom: 10px;">
                {{--                <form method="get"  action="">--}}
                <div class="layui-inline">
                    <input type="text" name="id" class="layui-input" id="id" placeholder="会员UID">
                </div>
                <div class="layui-inline">
                    <input type="text" name="team_mark" class="layui-input" id="team_mark" placeholder="团队标识">
                </div>
                <div class="layui-inline">
                    <input type="text" name="keyword" class="layui-input" id="keyword" placeholder="钱包地址">
                </div>
                <div class="layui-inline">
                    <input type="text" name="parent_id" class="layui-input" id="parent_id" placeholder="推荐人UID">
                </div>
                <div class="layui-inline">
                    <select name="farm_grade" xm-select="farm_grade" id="farm_grade" lay-verify="required"
                            class="layui-select">
                        <option value="">--等级--</option>
                        @foreach ($grade_list as $key=>$grand)
                            <option value="{{ $key }}">{{ $grand }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="layui-inline">
                    <input type="text" name="created_at" class="layui-input" id="created_at" style="width: 250px"
                           placeholder="注册时间">
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
                {{-- <button class="layui-btn" data-type="export">导出</button>--}}
            </div>


            <script type="text/html" id="toolColumn">

                <a class="layui-btn layui-btn-xs" lay-event="edit_user">编辑</a>
                <a class="layui-btn layui-btn-xs" lay-event="authority">权限</a>
                <a class="layui-btn layui-btn-xs" lay-event="parent">所有上级</a>
                <a class="layui-btn layui-btn-xs" lay-event="edit_wallet">编辑钱包地址</a>

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
                url: '{{ route('m.user.api.admin.api.user.index') }}',
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
                    {field: 'right', title: '操作', toolbar: '#toolColumn', width: 200},
                    {field: 'show_userid', title: 'UID', width: 80},
                    {field: 'username', title: '钱包地址', width: 200},
                    {
                        field: 'parent_id', title: '推荐人ID', event: 'show_parent', width: 100, templet: function (res) {
                            return res.parent ? res.parent.show_userid : '';
                        }
                    },
                    {field: 'team_count', title: '社群人数', width: 120},
                    {field: 'son_count', title: '直推人数', width: 120},
                    {field: 'grade_text', title: '等级', width: 100},
                    {field: 'team_mark', title: '团队标识', width: 100},
                    {
                        field: 'created_at', title: '注册时间', width: 170, templet: function (res) {
                            return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                        }
                    }
                ]],
                text: {
                    none: '没有可用数据'
                },
                limit: 12
            });
            table.on("tool(lay-table)", function (e) {
                // if (events[e.event]) {
                //     events[e.event].call(this, e.data);
                // }
                var data = e.data;
                if (e.event === 'edit_user') {

                    var url = '{{ route('m.user.admin.user.edit_user') }}?user_id=' + data.user_id;

                    layer.open({
                        type: 2
                        , title: '编辑会员：' + data['show_userid']
                        , content: url
                        , area: ['90%', '90%']
                    })

                } else if (e.event === 'show_parent') {

                    if (data.parent_id > 0) {
                        var html = "<div style='padding-left: 10px'>" +
                            "<p>UID：" + data.parent.show_userid + "</p>" +
                            "<p>钱包地址：" + data.parent.address + "</p>" +
                            "<p>等级：" + data.parent.grade_text + "</p>" +
                            "<p>注册时间：" + data.parent.created_at + "</p>" +
                            "</div>";
                        layer.open({
                            type: 1,
                            shade: 0.8,
                            offset: 'auto',
                            area: [600 + 'px', 550 + 'px'], // area: [width + 'px',height+'px'] //原图显示
                            shadeClose: true,
                            scrollbar: false,
                            title: "推荐人信息", //不显示标题
                            content: html, //捕获的元素，注意：最好该指定的元素要存放在body最外层，否则可能被其它的相对元素所影响
                        });
                    } else {
                        layer.msg('无推荐人');
                    }

                } else if (e.event === 'parent') {

                    var url = '{{ route('m.user.admin.user.parent_all') }}?id=' + data.user_id;
                    layer.open({
                        type: 2
                        , title: '所有上级：' + data['show_userid']
                        , content: url
                        , area: ['90%', '90%']
                    })

                } else if (e.event === 'authority') {

                    var url = '{{ route('m.user.admin.user.authority') }}?user_id=' + data.user_id;

                    layer.open({
                        type: 2
                        , title: '会员权限：' + data['show_userid']
                        , content: url
                        , area: ['90%', '90%']
                    })

                }
            });


            let active = {
                reload: function () {
                    let keyword = $('#keyword');
                    let id = $('#id');
                    let created_at = $('#created_at');
                    let is_export = $("input[type='checkbox']").is(':checked');
                    let team_mark = $('#team_mark');
                    console.log(is_export);
                    //执行重载
                    table.reload('dataTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        , where: {
                            id: id.val(),
                            keyword: keyword.val(),
                            created_at: created_at.val(),
                            fund_grade: $('#fund_grade').val(),
                            is_site: $('#is_site').val(),
                            parent_id: $('#parent_id').val(),
                            farm_grade: $('#farm_grade').val(),
                            team_mark: team_mark.val(),
                            is_export: is_export
                        }
                    }, 'data');
                },

            };


            $('.layui-btn').on('click', function () {
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        })


    </script>
@endpush

