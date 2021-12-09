@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">

            <blockquote class="layui-elem-quote">会员UID：{{$user->show_userid}}、钱包地址：{{$user->address}}的所有上级推荐人
            </blockquote>
            <ul class="layui-timeline">
                @foreach ($data as $key => $vo)
                    <li class="layui-timeline-item">
                        <i class="layui-icon layui-timeline-axis">&#xe61a;</i>
                        <div class="layui-timeline-content layui-text">
                            <p>
                                <span class="layui-badge">第{{$count-$key}}代</span>会员名：{{$vo->address}}、UID：{{$vo->show_userid}}
                                <br>等级：{{$vo->grade_text}}
                                <br>注册时间：{{$vo->created_at}}
                            </p>
                        </div>
                    </li>
                @endforeach
                <li class="layui-timeline-item">
                    <i class="layui-icon layui-timeline-axis">&#xe61a;</i>
                    <div class="layui-timeline-content layui-text">
                        <p>
                            <span class="layui-badge">自己</span>钱包地址：{{$user->address}}、UID：{{$user->show_userid}}
                            <br>等级：{{$user->grade_text}}
                            <br>注册时间：{{$user->created_at}}
                        </p>
                    </div>
                </li>
            </ul>

        </div>
    </div>
@endsection


@push('after-scripts')


    <script>

        layui.use(['form', 'table', 'util', 'laydate', 'laytpl'], function () {

            var $ = layui.$
                , util = layui.util
                , form = layui.form
                , table = layui.table
                , laytpl = layui.laytpl
                , laydate = layui.laydate;
        })
    </script>
@endpush

