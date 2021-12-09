@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <div class="test-table-reload-btn" style="margin-bottom: 10px;">
                <div class="layui-inline">
                    <select name="types" xm-select="types" id="types" lay-filter="types"
                            lay-verify="required" class="layui-select">
                        <option value="">--类型--</option>
                        @foreach ($types as $key=>$vo)
                            <option value="{{ $key }}">{{ $vo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="layui-inline">
                    <select name="state" xm-select="state" id="state" lay-filter="state"
                            lay-verify="required" class="layui-select">
                        <option value="">--状态--</option>
                        <option value="1">开启中</option>
                        <option value="2">已关闭</option>
                    </select>
                </div>
                <div class="layui-inline">
                    <input type="text" name="user_id" class="layui-input" id="user_id" placeholder="用户ID">
                </div>

                <button class="layui-btn" data-type="reload">搜索</button>
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
                url: '{{ route('m.user.api.admin.api.automatic.index') }}',
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
                    {field: 'id', title: 'ID', width: 100},
                    {
                        field: 'show_userid', title: '用户ID', width: 140, templet: function (res) {
                            return res.user.show_userid
                        }
                    },
                    {
                        field: 'automatic', title: '状态', width: 140, templet: function (res) {
                            return res.automatic === 1 ? '<span class="layui-badge layui-bg-green">开启</span>' :
                                '<span class="layui-badge">关闭</span>';
                        }
                    },
                    {field: 'type_text', title: '类型', width: 160},
                    {field: 'principal', title: '本金', width: 120},
                    {field: 'total_days', title: '收益天数', width: 120},
                    {
                        field: 'rate_of_return', title: '每日收益率', width: 100, templet: function (res) {
                            return res.rate_of_return + '%';
                        }
                    }
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
                if (e.event === 'info') {
                }
            });


            let active = {
                reload: function () {
                    let user_id = $('#user_id').val();
                    let types = $('#types').val();
                    let state = $('#state').val();
                    //执行重载
                    table.reload('dataTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        , where: {
                            types: types,
                            user_id: user_id,
                            state: state
                        }
                    }, 'data');
                }
            };


            $('.layui-btn').on('click', function () {
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        })


    </script>
@endpush

