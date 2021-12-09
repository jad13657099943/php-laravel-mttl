@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">

        <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin" style="padding: 20px 0 0 0;">

            <div style="border: 1px solid #399fff;padding: 10px;margin-bottom: 10px;">
                推荐人：<span id="parent_info"></span>
            </div>
            <form class="layui-form-item" action="{{ route('m.user.admin.user.tree') }}" method="get">

                <label class="layui-form-label" style="width: 120px;">搜索UID</label>
                <div class="layui-input-inline">
                    <input type="text" name="user_id" id="uid" value="{{$uid}}" lay-verify="required"  placeholder="请输入UID快速查找" autocomplete="off" class="layui-input">
                </div>
                <button class="layui-btn layui-btn-normal" lay-submit lay-filter="add" >
                    查询团队会员
                </button>
            </form>
        </div>

        <div class="layui-row layui-col-space15">
            <div class="layui-col-md6">
                {{--<p>数据顺序：会员UID—会员名—消费额—会员消费等级—会员推广等级</p>--}}

                @if ($son_num == 0)
                    <p style="color: red">该会员下面没有下级会员</p>
                @endif
                <ul id="treeDemo" class="ztree" style="margin-top: 15px;"></ul>

            </div>
        </div>

    </div>
@endsection

@push('after-scripts')
    <script src="/zTree/js/jquery.js"></script>
    <link rel="stylesheet" href="/zTree/css/zTreeStyle/zTreeStyle.css" media="all">
    <script src="/zTree/js/jquery.ztree.all.js" type="text/javascript" charset="utf-8"></script>

    <script type="text/javascript">
        var setting = {
            async: {
                enable: true,
                url:'{{ route('m.user.api.admin.api.user.tree') }}?uid={{$user_id}}',
                autoParam:["user_id"],
                dataFilter: filter
            }
        };

        /*function filter(treeId, parentNode, childNodes) {
            console.log(childNodes);
            if (!childNodes) return null;
            return childNodes['data'];
        }*/
        function filter(treeId, parentNode, childNodes) {
            console.log(childNodes);
            if (!childNodes) return null;
//			for (var i=0, l=childNodes.length; i<l; i++) {
//				childNodes[i].mobile = childNodes[i].mobile.replace(/\.n/g, '.');
//			}
            var data = childNodes['data'];
            $('#uid').val(childNodes['user_id']);
            console.log(childNodes['user_id']);
            $("#parent_info").html(childNodes['parent_info']);
            return childNodes['data'];
        }

        $(document).ready(function(){
            $.fn.zTree.init($("#treeDemo"), setting);
        });

    </script>
    <script>
        layui.use(['form', 'table', 'util', 'laydate'], function () {

            var $ = layui.$
                , util = layui.util
                , form = layui.form
                , table = layui.table
                , laydate = layui.laydate;

        })
    </script>
@endpush
