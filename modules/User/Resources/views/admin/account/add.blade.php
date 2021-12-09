@extends('core::admin.layouts.app')

@section('content')
    <form class="layui-form" action="">

    <div class="layui-card">
        <div class="layui-card-body">
            {{csrf_field()}}
            <div class="layui-form-item">
                <label class="layui-form-label">账号</label>
                <div class="layui-input-inline">
                    <input type="text" id="username" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">密码</label>
                <div class="layui-input-inline">
                    <input type="text" id="password" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">权限</label>
                <div class="layui-input-block">
                    <select id="admin_id" name="interest" lay-filter="aihao">
                        <option value=""></option>
                        @foreach($list as $item)
                            <option value={{$item->id}}>{{$item->name}}</option>
                            @endforeach
                    </select>
                </div>
            </div>
            <div id="test12" class="demo-tree-more"></div>
            <div class="layui-form-item">
                <label class="layui-form-label"></label>
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit lay-filter="add" id="add">立即提交</button>
                </div>
            </div>

        </div>
    </div>
    </form>

@endsection

@push('after-scripts')
    <script>
        layui.use(['tree', 'util'], function() {
            var $ = layui.$
                , layer = layui.layer
                , util = layui.util
            $('#add').click(function () {
                var url='{{route('m.user.api.admin.api.account.add')}}';
                var username=$('#username').val();
                var password=$('#password').val();
                var admin_id=$('#admin_id').val();
                var data={username:username,password:password,admin_id:admin_id};
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'post',
                    data:data,
                    async:false,
                    success: function (src) {

                            window.parent.location.reload();

                    },
                    error:function (data) {
                        layer.msg(data.responseJSON.message);
                    }
                });
            })
        });

    </script>
@endpush

<style>
    .layui-form-item .layui-input-inline {
        width: 800px !important;
    }

    .layui-form-label {
        box-sizing: initial;
        width: 200px !important;
    }
</style>
