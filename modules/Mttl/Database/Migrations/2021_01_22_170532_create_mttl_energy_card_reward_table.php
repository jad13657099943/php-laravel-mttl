<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMttlEnergyCardRewardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 能量卡奖励发放记录表
        Schema::create('mttl_energy_card_reward', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('buy_record_id')->comment('购买记录id，关联ti_mttl_energy_crad_buy');
            $table->bigInteger('user_id')->comment('用户id，关联ti_users');
            $table->decimal('principal', 12, 4)->comment('本金');
            $table->decimal('daily_rate', 12, 6)->default(0)->comment('每日收益率');
            $table->decimal('amount', 12, 4)->default(0)->comment('奖励金额');
            $table->tinyInteger('state')->default(0)->comment('状态');

            $table->dateTimeTz('created_at');
            $table->dateTimeTz('updated_at');

            $table->index('buy_record_id');
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
        Schema::dropIfExists('mttl_energy_card_reward');
    }
}
