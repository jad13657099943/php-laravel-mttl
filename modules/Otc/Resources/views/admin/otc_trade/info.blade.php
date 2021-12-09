@extends('core::admin.layouts.app')
@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">数据详情</div>
                    <div class="layui-card-body">
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th colspan="3">订单数据</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>信息ID：{{$info->id}}</td>
                                <td>订单号：{{$info->no}}</td>
                                <td>币种：{{$info->coin}}</td>
                            </tr>

                            <tr>
                                <td>买家会员：{{$info->buyerUser->user->username}}【 UID：{{ $info->buyerUser->user->id  }}】 </td>
                                <td>卖家会员：{{$info->sellerUser->user->username}}【 UID：{{ $info->sellerUser->user->id  }}】 </td>
                                <td>退回|申诉原因：{{ $info->reason }}</td>
                            </tr>

                            <tr>
                                <td>挂单类型：{{$info->exchange_type_text}}</td>
                                <td>撮合单类型：{{$info->type_text}}</td>
                                <td>交易状态：{{$info->status_text}}</td>
                            </tr>

                            <tr>
                                <td>买卖数量：{{$info->num}}</td>
                                <td>单笔最小交易量：{{$info->min}}</td>
                                <td>单笔最大交易量：{{$info->max}}</td>
                            </tr>

                            <tr>
                                <td>创建时间：{{$info->created_at->format('Y-m-d H:i:s')}}</td>
                                <td>更新时间：{{$info->updated_at->format('Y-m-d H:i:s')}}</td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>支付时间：{{$info->paid_at}}</td>
                                <td>超时时间：{{$info->expired_at}}</td>
                                <td>确认收款时间：{{$info->confirmed_at}}</td>
                            </tr>


                            @if ($info->bank_type !='')
                            <tr>
                                <td>收付款方式：{{$info->bank_type}}</td>
                                @if (isset($info->bank['value']) )
                                <td>
                                    收款人：{{$info->bank['value']['true_name']}}
                                    账号：{{$info->bank['value']['account']}}
                                </td>
                                <td><img src="{{$info->bank['value']['url']}}"></td>
                                @endif
                            </tr>
                            @endif

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


<script src="/vendor/js/jquery.min.js"></script>
<script src="/vendor/js/jquery.qrcode.min.js"></script>


@push('after-scripts')


    @push('after-scripts')

        <script>

            function ityzl_SHOW_LOAD_LAYER(){
                return layer.msg('处理中...', {icon: 16,shade: [0.5, '#b2b2b2'],scrollbar: false, time:0}) ;
            }
            function ityzl_CLOSE_LOAD_LAYER(index){
                layer.closeAll();
                layer.close(index);
            }

            layui.use(['form', 'table', 'util','laydate'], function () {

                var $ = layui.$
                    , util = layui.util
                    , form = layui.form
                    , table = layui.table
                    , laydate = layui.laydate;


                form.on('submit(add)', function(data){


                    var url = '{{ route('m.coin.api.admin.api.coin_trade.manual') }}';
                    console.log(url);
                    $.post(url,data.field,function(res){

                        console.log(res);

                        //if(res.code==200){

                        layer.msg(res.msg,{icon: 1,time: 2000,shade: [0.8, '#393D49']},function(){

                            window.parent.location.reload();
                        });

                        /*}else{
                            layer.msg(res.msg, {time: 2000});
                        }*/
                    },'json');
                    return false;
                });

            })
        </script>
    @endpush
