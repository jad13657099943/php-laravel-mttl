<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtcTradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otc_trades', function (Blueprint $table) {
            $table->increments('id')->comment('OTC撮合订单');
            $table->unsignedInteger('otc_exchange_id')->nullable()->comment('挂单id号');
            $table->string('no', 128)->nullable()->default()->comment('撮合订单号');
            $table->unsignedTinyInteger('exchange_type')->default(\Modules\Otc\Models\OtcTrade::TYPE_SELL)->comment('挂单类型 1-卖单 2-买单');
            $table->unsignedTinyInteger('type')->default(\Modules\Otc\Models\OtcTrade::TYPE_SELL)->comment('撮合订单类型 1-卖单 2-买单');
            $table->unsignedInteger('buyer_id')->nullable()->comment('购买用户id');
            $table->unsignedInteger('seller_id')->nullable()->comment('卖家用户id');
            $table->string('coin', 20)->nullable()->default('')->comment('币种');
            $table->unsignedDecimal('num', 20, 10)->nullable()->default(0)->comment('买卖数量');
            $table->unsignedDecimal('price', 10, 4)->nullable()->default(0)->comment('币种单价');
            $table->unsignedDecimal('min', 10, 4)->nullable()->default(1)->comment('单笔最小交易量');
            $table->unsignedDecimal('max', 10, 4)->nullable()->default(1)->comment('单笔最大交易量');
            $table->tinyInteger('status')->default(\Modules\Otc\Models\OtcTrade::STATUS_WAITING)->comment('交易状态 -1退回失败0-待付款 1-已付款/待确认 2-已确认/交易结束 8-买家申诉 9-卖家申诉 10-取消');
            $table->string('reason', 255)->nullable()->comment('原因, 退回原因, 申诉原因');
            $table->dateTime('expired_at')->nullable()->comment('超时时间');
            $table->dateTime('paid_at')->nullable()->comment('支付时间');
            $table->text('bank')->nullable()->comment('收款账号');
            $table->dateTime('confirmed_at')->nullable()->comment('确认收款时间');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otc_trades');
    }
}
