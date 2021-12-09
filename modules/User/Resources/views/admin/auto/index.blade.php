@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">


            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

              {{--  <div class="layui-inline">
                    <input type="text" name="admin" class="layui-input" id="admin" placeholder="管理员">
                </div>--}}

                <div class="layui-inline">
                    <input type="text" name="user" class="layui-input" id="user" placeholder="会员UID">
                </div>

              {{--  <div class="layui-inline">
                    <label class="layui-label">操作类型</label>
                    <select name="type" xm-select="type" id="type" lay-verify="required" class="layui-select" >
                        <option value=""></option>
                        @foreach ($list as $key=>$grand)
                            <option value="{{ $key }}">{{ $grand }}</option>
                        @endforeach
                    </select>
                </div>--}}
                {{--<div class="layui-inline">
                    <input type="text" name="created_at" class="layui-input" id="created_at" style="width: 250px"
                           placeholder="时间">
                </div>--}}
                <button class="layui-btn" data-type="reload">搜索</button>
            </div>
            <script type="text/html" id="toolColumn">


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
                url: '{{ route('m.user.api.admin.api.auto.index') }}',
                toolbar: '#toolbarDemo' //开启头部工具栏，并为其绑定左侧模板
                ,defaultToolbar: ['exports'],
                parseData: function (res) { //res 即为原始返回的数据
                    return {
                        'code': res.message ? 400 : 0, //解析接口状态
                        'msg': res.message || '加载失败', //解析提示文本
                        'count': res.total, //解析数据长度
                        'data': res.data || [] //解析数据列表
                    };
                },
                page: true,
                id: "dataTable",
                cols: [[
                    //  {field: 'right', title: '操作', toolbar: '#toolColumn', width: 120},
                    {field: 'show', title: '用户ID', width: 250},
                    {field: 'type_text', title: '操作类型', width: 250},
                    {field: 'log', title: '操作详情', width: 250},
                    {
                        field: 'created_at', title: '操作时间', width: 270, templet: function (res) {
                            return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                        }
                    },
                ]],
                text: {
                    none: '没有可用数据'
                },
            });
            $(".export").click(function () {
                table.exportFile(ins1.config.id, exportData, 'xls');
            });
            table.on("tool(lay-table)", function (e) {
                // if (events[e.event]) {
                //     events[e.event].call(this, e.data);
                // }
                var data = e.data;
                if (e.event === 'edit_user') {

                }
                if (e.event==='delete'){

                }
            });


            let active = {
                reload: function () {
                    let user = $('#user');
                  //  let admin = $('#admin');
                   // let type = $('#type');
                    //执行重载
                    table.reload('dataTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        , where: {
                            user: user.val(),
                         //   admin: admin.val(),
                          //  type:type.val()
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

