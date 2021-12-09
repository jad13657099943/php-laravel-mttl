@extends('core::admin.layouts.app')

@section('content')
    <div class="layui-card">
        <div class="layui-card-body">
            <div style="margin-bottom: 20px;">
                <div class="layui-inline">
                    <input type="text" placeholder="团队标识" class="layui-input" id="team_mark" value="{{$param['mark']}}"/>
                </div>
                <div class="layui-inline">
                    <input type="text" name="user" class="layui-input" id="user" value="{{$show}}" placeholder="会员UID">
                </div>
                <button type="button" class="layui-btn">搜索</button>
            </div>
            <table class="layui-table">
                <thead>
                <tr>
                    <th>会员总人数</th>
                    <th>今日新增</th>
                    <th>本周新增</th>
                    <th>本月新增</th>
                    <th>本年新增</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{$user['total']}}</td>
                    <td>{{$user['day']}}</td>
                    <td>{{$user['week']}}</td>
                    <td>{{$user['month']}}</td>
                    <td>{{$user['year']}}</td>
                </tr>
                </tbody>
            </table>

            <table class="layui-table">
                @foreach ($coin as $key=> $item)
                    <thead>
                    <tr>
                        <th colspan="5">{{$key}} 总数：{{$item['balance']}}、后台拨币总增加：{{$item['admin_add_num']}}
                            、后台拨币总减少：{{$item['admin_dec_num']}}</th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>总充值笔数：{{$item['recharge']['total']['num']}}
                            、总充值数：{{$item['recharge']['total']['total']}}</td>
                        <td>今日充值笔数：{{$item['recharge']['day']['num']}}、今日充值数：{{$item['recharge']['day']['total']}}</td>
                        <td>本周充值笔数：{{$item['recharge']['week']['num']}}
                            、本周充值数：{{$item['recharge']['week']['total']}}</td>
                        <td>本月充值笔数：{{$item['recharge']['month']['num']}}
                            、本月充值数：{{$item['recharge']['month']['total']}}</td>
                        <td>本年充值笔数：{{$item['recharge']['year']['num']}}
                            、本年充值数：{{$item['recharge']['year']['total']}}</td>
                    </tr>
                    <tr>
                        <td>总提现笔数：{{$item['withdraw']['total']['num']}}
                            、总提现数：{{$item['withdraw']['total']['total']}}</td>
                        <td>今日提现笔数：{{$item['withdraw']['day']['num']}}、今日提现数：{{$item['withdraw']['day']['total']}}</td>
                        <td>本周提现笔数：{{$item['withdraw']['week']['num']}}
                            、本周提现数：{{$item['withdraw']['week']['total']}}</td>
                        <td>本月提现笔数：{{$item['withdraw']['month']['num']}}
                            、本月提现数：{{$item['withdraw']['month']['total']}}</td>
                        <td>本年提现笔数：{{$item['withdraw']['year']['num']}}
                            、本年提现数：{{$item['withdraw']['year']['total']}}</td>
                    </tr>
                    </tbody>
                @endforeach
            </table>

            <table class="layui-table">

                <thead>
                @foreach ($grade as $item)
                    <tr>
                        <th>{{$item['name']}}：{{$item['num']}} 人</th>
                    </tr>
                @endforeach
                </thead>
            </table>


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

            $('.layui-btn').on('click', function () {
                let mark = $('#team_mark').val();
                let user=$('#user').val();
                console.log(mark);
                if (mark) {
                    window.location.href = "/m/user/admin/total/total?mark="+mark;
                }
                if(user){
                    window.location.href = "/m/user/admin/total/total?user="+user;
                }
                if (mark===''&&user===''){
                    window.location.href = "/m/user/admin/total/total";
                }
            });
        })


    </script>
@endpush

