@extends('core::admin.layouts.app')

@section('content')



    <div class="layui-card">
        <div class="layui-card-body">
            {{csrf_field()}}
            <div class="layui-form-item">
                <label class="layui-form-label">角色名称</label>
                <div class="layui-input-inline">
                    <input type="text" id="name" value="{{$list->name}}" autocomplete="off" class="layui-input">
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


@endsection

@push('after-scripts')
    <script>
        var url='{{route('m.user.api.admin.api.admin.menus')}}';
        var menus=[];
        $.ajax({
            url: url,
            dataType: 'json',
            type: 'post',
            data:{},
            async:false,
            success: function (src) {
                menus= src.list;
            },
            error:function (data) {
                alert('请求失败');
            }
        });

        layui.use(['tree', 'util'], function() {
            var tree = layui.tree
                , layer = layui.layer
                , util = layui.util
                ,data =menus;
            tree.render({
                elem: '#test12'
                , data: data
                , showCheckbox: true  //是否显示复选框
                , id: 'demoId1'
                , isJump: true //是否允许点击节点时弹出新窗口跳转
                , click: function (obj) {
                    var data = obj.data;  //获取当前点击的节点数据
                    layer.msg( JSON.stringify(data.title));
                }
            });
            function ajax(url,data){
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
            }
            function ajaxs(url,data){
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'post',
                    data:data,
                    async:false,
                    success: function (src) {
                        if (src.msg!=undefined) {
                            alert(src.msg);
                        }
                        window.parent.location.reload();
                    },
                    error:function (data) {
                        layer.msg(data.responseJSON.message);
                    }
                });
            }
            $('#add').click(function () {
                var checkedData = tree.getChecked('demoId1'); //获取选中节点的数据
                var url='{{route('m.user.api.admin.api.admin.edit')}}?id='+{{$list->id}};
                var name=$('#name').val();
                var data={data:checkedData,name:name};
                ajaxs(url,data);
            })
            var urls='{{route('m.user.api.admin.api.admin.detail')}}';
            var menuss=[];
            var ids={{$list->id}};
            $.ajax({
                url: urls,
                dataType: 'json',
                type: 'post',
                data:{id:ids},
                async:false,
                success: function (src) {
                    menuss= src.list;
                },
                error:function (data) {
                    layer.msg(data.responseJSON.message);
                }
            });
            tree.setChecked('demoId1', menuss);

            util.event('lay-demo', {
                getChecked: function(othis){

                }
                ,setChecked: function(){
                    ; //勾选指定节点
                }
                ,reload: function(){
                    //重载实例
                    tree.reload('demoId1', {

                    });

                }
            });
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
