@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">


            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <div class="layui-inline">
                    <input type="text" name="user" class="layui-input" id="user" placeholder="会员UID">
                </div>
                <div class="layui-inline">
                    <input type="text" name="team_mark" class="layui-input" id="team_mark" placeholder=" 团队标识 ">
                </div>
                <div class="layui-inline">
                    <label class="layui-label">状态</label>
                    <select name="type" xm-select="type" id="type" lay-verify="required" class="layui-select" >
                        @foreach ($list as $key=>$grand)
                            <option value="{{ $key }}">{{ $grand }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
            </div>
            <script type="text/html" id="toolColumn">
                <a class="layui-btn layui-btn-xs" lay-event="edit">编辑释放</a>
                <a class="layui-btn layui-btn-xs" lay-event="edit2">编辑质押</a>
                <a class="layui-btn layui-btn-xs" lay-event="del">删除</a>
            </script>
            <div class="layui-btn-group demoTable">
                {{--<button class="layui-btn" id="edit_key" data-type="getCheckData">设置权限</button>--}}
                <button class="layui-btn" id="edit_status" data-type="getCheckLength">设置状态</button>
            </div>
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
                url: '{{ route('m.user.api.admin.api.check.list') }}',
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
                    {type:'checkbox'},
                    //  {field: 'right', title: '操作', toolbar: '#toolColumn', width: 120},
                    {field: 'show_userid', title: '用户ID', width: 200},
                    {field: 'check_text', title: '状态', width: 150},
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
                    let type = $('#type');
                    let team_mark=$('#team_mark');
                    //  let admin = $('#admin');
                    // let type = $('#type');
                    //执行重载
                    table.reload('dataTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        , where: {
                            show: user.val(),
                            check: type.val(),
                            team_mark:team_mark.val(),
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

            table.on('checkbox(dataTable)', function(obj){
                console.log(obj)
            });

            $('#edit_key').click(function(){
                layer.open({
                    title:'设置权限',
                    content:  '<div class="layui-inline">'+
                        '<label class="layui-label">权限</label>'+
                        '<div class="layui-inline">'+
                        '<select id="status" class="layui-input" lay-filter="aihao" style="width: 150px">'+
                        '<option value="1">关闭</option>'+
                        '<option value="2">开启</option>'+
                        '</select>'+
                        '</div>'+
                        '</div>'
                    ,btn: ['确定']
                    ,yes: function(index, layero){
                        let id=new Array();
                        var checkStatus = table.checkStatus('dataTable')
                            ,data = checkStatus.data;
                        for (var i in data){
                            id[i]=data[i].user_id;
                        }
                        if (id==''){
                            layer.msg('请选择用户!');
                            return;
                        }
                        let status=$('#status').val();
                        let datas={id:id,status:status};
                        let URL='{{ route('m.user.api.admin.api.key.status') }}';
                        ajax(URL,datas);
                    }
                    ,cancel: function(){
                        //右上角关闭回调

                        //return false 开启该代码可禁止点击该按钮关闭
                    }
                });
            });

            $('#edit_status').click(function(){
                layer.open({
                    title:'设置状态',
                    content:  '<div class="layui-inline">'+
                        '<label class="layui-label">状态</label>'+
                        '<div class="layui-inline">'+
                        '<select id="status" class="layui-input" lay-filter="aihao" style="width: 150px">'+
                        '<option value="2">开启</option>'+
                        '<option value="1">关闭</option>'+
                        '</select>'+
                        '</div>'+
                        '</div>'
                    ,btn: ['确定']
                    ,yes: function(index, layero){
                        let id=new Array();
                        var checkStatus = table.checkStatus('dataTable')
                            ,data = checkStatus.data;
                        for (var i in data){
                            id[i]=data[i].user_id;
                        }
                        if (id==''){
                            layer.msg('请选择用户!');
                            return;
                        }
                        let status=$('#status').val();
                        let datas={id:id,check:status};
                        let URL='{{route('m.user.api.admin.api.check.set')}}';
                        ajax(URL,datas);
                    }
                    ,cancel: function(){
                        //右上角关闭回调

                        //return false 开启该代码可禁止点击该按钮关闭
                    }
                });
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
                        layer.msg('操作失败');
                    }
                });
            }
        })


    </script>
@endpush

