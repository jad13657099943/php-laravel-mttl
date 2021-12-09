<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMttlEnergyCardBuyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 能量卡购买记录表
        Schema::create('mttl_energy_card_buy', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('user_id')->comment('用户id，关联ti_users');
            $table->tinyInteger('types')->comment('能量卡类型');
            $table->decimal('principal', 12, 4)->comment('本金');
            $table->decimal('daily_rate', 12, 6)->default(0)->comment('每日收益率');
            $table->tinyInteger('total_days')->comment('可收益天数');
            $table->tinyInteger('issued_days')->comment('已发放天数');
            $table->tinyInteger('surplus_days')->comment('剩余天数');
            $table->decimal('issued_amount', 12, 4)->default(0)->comment('已发放奖励金额');
            $table->dateTimeTz('begin_date')->comment('开始时间');
            $table->dateTimeTz('finish_date')->comment('结束时间');
            $table->boolean('automatic')->default(0)->comment('是否自动购买');
            $table->dateTime('last_releast_time')->nullable()->comment('最后释放时间');

            $table->dateTimeTz('created_at');
            $table->dateTimeTz('updated_at');

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mttl_energy_card_buy');
    }
}
