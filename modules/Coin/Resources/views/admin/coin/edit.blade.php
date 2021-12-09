@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <form method="post" class="layui-form">
                {{csrf_field()}}


                <div class="layui-form-item">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-inline">
                        <input type="text" disabled value="{{$info->symbol}}"
                               required   autocomplete="off" class="layui-input">
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label">提币状态</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="withdraw_state" value="0" title="暂停提币" @if($info->withdraw_state==0)  checked @endif>
                        <input type="radio" name="withdraw_state" value="1" title="需人工审核" @if($info->withdraw_state==1)  checked @endif>
                        <input type="radio" name="withdraw_state" value="2" title="无需审核" @if($info->withdraw_state==2)  checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">提币最小额度</label>
                    <div class="layui-input-inline">
                        <input type="text" name="withdraw_min" value="{{ floatval($info->withdraw_min) }}" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">提币最大额度</label>
                    <div class="layui-input-inline">
                        <input type="text" name="withdraw_max" value="{{ floatval($info->withdraw_max) }}" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">提币手续费(%)</label>
                    <div class="layui-input-inline">
                        <input type="text" name="withdraw_fee" value="{{ floatval($info->withdraw_fee) }}" autocomplete="off" class="layui-input">
                    </div>
                    <div>提现费用,单位为该币种。</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">充值状态</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="recharge_state" value="0" title="暂停充币" @if($info->recharge_state==0)  checked @endif>
                        <input type="radio" name="recharge_state" value="1" title="开启充币" @if($info->recharge_state==1)  checked @endif>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">充值入账最小金额</label>
                    <div class="layui-input-inline">
                        <input type="text" name="recharge_min" value="{{ floatval($info->recharge_min) }}" autocomplete="off" class="layui-input">
                    </div>
                    <div>小于该值不入账。</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">充值归集最小金额</label>
                    <div class="layui-input-inline">
                        <input type="text" name="cold_min" value="{{ floatval($info->cold_min) }}" autocomplete="off" class="layui-input">
                    </div>
                    <div>小于该值不归集。</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">内部转账状态</label>
                    <div class="layui-input-inline">
                        <input type="radio" name="internal_state" value="0" title="暂停" @if($info->internal_state==0)  checked @endif>
                        <input type="radio" name="internal_state" value="1" title="开启" @if($info->internal_state==1)  checked @endif>
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label">加速旷工费</label>
                    <div class="layui-input-inline">
                        <input type="text" name="gas_price" value="{{ floatval($info->gas_price) }}" autocomplete="off" class="layui-input">
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-inline">

                        <input type="hidden" name="id" value="{{ $info->id }}">
                        <button class="layui-btn" lay-submit lay-filter="add">立即提交</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection

@push('after-scripts')
    <script>
        layui.use(['form', 'table', 'layedit','laydate'], function () {
            var $ = layui.$
                , layedit = layui.layedit
                , form = layui.form
                , table = layui.table
                , laydate = layui.laydate;
            laydate.render({
                elem: '#start_time'
                ,type:'datetime'
            });
            laydate.render({
                elem: '#end_time'
                ,type:'datetime'
            });


            form.on('submit(add)', function(data){

                var url = '{{ route('m.coin.api.admin.api.coin.edit') }}';
                $.post(url,data.field,function(res){

                    console.log(res);
                    layer.msg(res.msg,{icon: 1,time: 2000,shade: [0.8, '#393D49']},function(){

                        window.parent.location.reload();
                    });
                },'json');
                return false;
            });


        })
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
