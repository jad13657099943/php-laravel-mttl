@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">

                <div class="layui-inline">
                    <input type="text" name="user_info" class="layui-input" id="user_info" placeholder=" 会员ID|会员名 ">
                </div>
                <button class="layui-btn" data-type="reload">搜索</button>
            </div>

            <table id="LAY-user-back-role" lay-filter="LAY-user-back-role"></table>


        </div>
    </div>
@endsection
@push('after-scripts')
    <script type="text/html" id="table-useradmin-admin">


        {{--@{{# if(d.state == 2 ){ }}
            <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info_link">链上详情</a>
        @{{# } }}--}}
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit_wallet">编辑钱包地址</a>

    </script>

    @push('after-scripts')


        <script>

            function ityzl_SHOW_LOAD_LAYER() {
                return layer.msg('处理中...', {icon: 16, shade: [0.5, '#b2b2b2'], scrollbar: false, time: 0});
            }

            function ityzl_CLOSE_LOAD_LAYER(index) {
                layer.closeAll();
                layer.close(index);
            }

            layui.use(['form', 'table', 'util', 'laydate', 'laytpl'], function () {

                var $ = layui.$
                    , util = layui.util
                    , form = layui.form
                    , table = layui.table
                    , laytpl = layui.laytpl
                    , laydate = layui.laydate;

                laydate.render({
                    elem: '#laydate-range-datetime'
                    , type: 'date'
                    , range: '||'
                });

                table.render({
                    elem: '#LAY-user-back-role',
                    toolbar: '#tableToolbar',
                    url: '{{ route('m.coin.api.admin.api.user_wallet.index') }}',
                    method: 'get',
                    parseData: function (res) { //res 即为原始返回的数据
                        return {
                            'code': res.message ? 400 : 0, //解析接口状态
                            'msg': res.message || '加载失败', //解析提示文本
                            'count': res.total || 0, //解析数据长度
                            'data': res.data || [] //解析数据列表
                        };
                    },
                    cols: [[

                        {title:'操作', width:120,toolbar: '#table-useradmin-admin' },
                        {field: 'id', title: 'ID', width: 100, sort: true}
                        , {
                            field: 'user_id', title: '会员ID', width: 100, sort: true, templet: function (res) {
                                return res.project_user.show_userid
                            }
                        }
                        , {field: 'user_name', title: '会员名', width: 150,}
                        , {field: 'chain', title: '主链', width: 120,}
                        , {field: 'address', title: '钱包地址', width: 400,}
                        , {
                            field: 'created_at', title: '创建时间', width: 170, sort: true, templet: function (res) {
                                return moment(res.created_at).format("YYYY-MM-DD HH:mm:ss")
                            }
                        }
                    ]],
                    text: {
                        none: '无相关数据'
                    },
                    page: true
                });
                table.on("tool(LAY-user-back-role)", function (e) {
                    var data = e.data;
                    if (e.event === 'edit_wallet') {
                        layer.prompt({
                            title: '请输入钱包地址',
                        }, function(val, index, elem){
                            let url='{{route('m.coin.api.admin.api.user_wallet.edit_wallet')}}';
                            let datas={address:val,id:data.id};
                            ajax(url,datas);
                        });
                    }
                });
                util.event('lay-event', events);

                var events = {};

                //搜搜重载
                var $ = layui.$, active = {
                    reload: function () {


                        var user_info = $('#user_info').val();
                        //执行重载
                        table.reload('LAY-user-back-role', {
                            page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            , where: {
                                key: {
                                    user_info: user_info,
                                }
                            }
                        });
                    }
                };

                $('.test-table-reload-btn .layui-btn').on('click', function () {
                    var type = $(this).data('type');
                    active[type] ? active[type].call(this) : '';
                });

            })
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
                        alert('编辑失败');
                    }
                });
            }
        </script>
    @endpush

