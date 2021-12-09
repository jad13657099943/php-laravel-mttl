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
                                <td>申诉信息ID：{{$info->id}}</td>
                                <td>申诉会员：{{ $info->user->username }}【UID： {{$info->user_id}}】</td>
                                <td>申诉时间：{{$info->created_at->format('Y-m-d H:i:s')}}</td>
                            </tr>

                            <tr>
                                <td>处理结果：{{$info->status_text}} </td>
                                <td>申诉措辞：{{$info->reason}}</td>
                                <td>处理说明：{{$info->handle}}</td>
                            </tr>

                            @if(count($info->image_list)>0)
                                <tr>
                                    <td colspan="3">
                                        @foreach ($info->image_list as $item)
                                            <img src="{{ $item }}">
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>


                        @if($info->status==-1)

                            <form class="layui-form"  method="post">
                                {{csrf_field()}}

                                <div class="layui-form-item">
                                    <label class="layui-form-label">处理结果</label>
                                    <div class="layui-input-block">
                                        <input type="radio" name="status" value="1" title="驳回申诉">
                                        <input type="radio" name="status" value="2" title="通过申诉" checked>
                                    </div>
                                </div>


                                <div class="layui-form-item layui-form-text">
                                    <label class="layui-form-label">驳回说明</label>
                                    <div class="layui-input-block">
                                        <textarea name="handle" required placeholder="请输入驳回说明，通过可不填写" class="layui-textarea"></textarea>
                                    </div>
                                </div>

                                <div class="layui-form-item">
                                    <label  class="layui-form-label">
                                    </label>
                                    <input type="hidden"  name="id" value="{{$info->id}}" />
                                    <button class="layui-btn" lay-submit lay-filter="add">立即提交</button>
                                </div>

                            </form>
                        @endif

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
                return layer.msg('处理中...', {icon: 16,shade: [0.5, '#b2b2b2'],scrollbar: false, time:0}) ;
            }
            function ityzl_CLOSE_LOAD_LAYER(index){
                layer.closeAll();
                layer.close(index);
            }

            layui.use(['form', 'table', 'util','laydate'], function () {

                var $ = layui.$
                    , form = layui.form;


                form.on('submit(add)', function(data){


                    var url = '{{ route('m.otc.api.admin.api.appeal.edit') }}';
                    console.log(url);
                    $.post(url,data.field,function(res){

                        console.log(res);

                        //if(res.code==200){

                        layer.msg(res.msg,{icon: 1,time: 2000,shade: [0.8, '#393D49']},function(){

                            window.location.reload();
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
