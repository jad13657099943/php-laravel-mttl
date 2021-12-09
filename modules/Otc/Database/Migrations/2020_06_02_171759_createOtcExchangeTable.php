<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtcExchangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otc_exchanges', function (Blueprint $table) {
            $table->increments('id')->comment('OTC市场挂单表');
            $table->unsignedInteger('user_id')->nullable()->comment('用户ID');
            $table->string('no', 128)->nullable()->default('')->comment('订单号');
            $table->unsignedTinyInteger('type')->default()->comment('订单类型 1-卖单 2-买单');
            $table->unsignedDecimal('num', 20, 10)->nullable()->default(0)->comment('买卖数量');
            $table->unsignedDecimal('surplus', 20, 10)->nullable()->default(0)->comment('剩余数量');
            $table->string('coin', 20)->nullable()->default('')->comment('币种');
            $table->unsignedDecimal('price', 10, 4)->nullable()->default(0)->comment('人民币参考价格,=0则取接口实时价');
            $table->unsignedDecimal('min', 10, 4)->nullable()->default(1)->comment('单笔最小交易量');
            $table->unsignedDecimal('max', 10, 4)->nullable()->default(1)->comment('单笔最大交易量');
            $table->string('status')->default(\Modules\Otc\Models\OtcExchange::STATUS_NORMAL)->comment('交易状态0-正常交易 1-交易中 2-交易成功 9已取消');
            $table->text('bank_list')->nullable()->comment('收款账号');
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
        Schema::dropIfExists('otc_exchanges');
    }
}
