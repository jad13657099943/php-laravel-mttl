@extends('core::admin.layouts.app')
@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">
                        <button class="layui-btn" onclick="create()">添加系统钱包</button>
                    </div>
                    <div class="layui-card-body">
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <td>钱包作用</td>
                                <td>使用类型</td>
                                <td>主链</td>
                                <td>钱包</td>
                                <td>通知地址</td>
                                {{--<td>操作</td>--}}
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($list as $key=> $item)
                            <tr>
                                <td>{{ $item->remark  }}</td>
                                <td>{{ $item->type  }}</td>
                                <td>{{ $item->chain  }}</td>
                                <td><a href="{{ $item->address_link }}" title="点击跳转查询地址详情" target="_blank"> {{ $item->address  }} </a></td>
                                <td>{{ $item->notice  }}</td>
                                {{--<td>
                                    <button id="id_{{$item->id}}" type="button" class="layui-btn layui-btn-sm" onclick="editInfo({{$item->id}})">修改</button>
                                </td>--}}
                            </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('after-scripts')


    @push('after-scripts')


        <script>

            function ityzl_SHOW_LOAD_LAYER(){
                return layer.msg('获取中...', {icon: 16,shade: [0.5, '#b2b2b2'],scrollbar: false, time:0}) ;
            }
            function ityzl_CLOSE_LOAD_LAYER(index){
                layer.closeAll();
                layer.close(index);
            }

            layui.use(['form', 'table', 'util','laydate'], function () {

                var $ = layui.$
                    , form = layui.form
                window.editInfo = function(id) {

                    var url = '{{ route('m.coin.admin.asset.system_wallet.edit_info') }}';
                    layer.open({
                        type: 2
                        , title: "修改系统钱包"
                        , content: url
                        , area: ['90%', '90%']
                    })
                }


                window.create = function () {
                    var url = '{{ route('m.coin.admin.asset.system_wallet.create') }}';
                    layer.open({
                        type: 2
                        , title: "添加系统钱包"
                        , content: url
                        , area: ['90%', '90%']
                    })
                }

            });



        </script>



    @endpush



