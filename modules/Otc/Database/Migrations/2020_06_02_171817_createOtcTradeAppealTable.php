<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtcTradeAppealTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otc_trade_appeals', function (Blueprint $table) {
            $table->increments('id')->comment('交易投诉记录');
            $table->unsignedInteger('otc_trade_id')->nullable()->comment('撮合订单号');
            $table->unsignedInteger('user_id')->nullable()->comment('申诉用户id');
            $table->string('no', 128)->nullable()->default('')->comment('交易投诉记录');
            $table->unsignedTinyInteger('user_type')->default(\Modules\Otc\Models\OtcTradeAppeal::USER_TYPE_SELLER)->comment('申诉用户身份，1-卖家，2-买家');
            $table->unsignedTinyInteger('type')->default(\Modules\Otc\Models\OtcTradeAppeal::TYPE_SELL)->comment('撮合订单类型 1-卖单 2-买单');
            $table->text('image_list')->nullable()->comment('证据截图');
            $table->string('reason', 255)->comment('措辞');
            $table->string('handle', 255)->nullable()->comment('处理说明');
            $table->tinyInteger('status')->default(\Modules\Otc\Models\OtcTradeAppeal::STATUS_WAITING)->comment('处理结果，-1-未处理，1-已驳回，2-已通过');
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
        Schema::dropIfExists('otc_trade_appeals');
    }
}
