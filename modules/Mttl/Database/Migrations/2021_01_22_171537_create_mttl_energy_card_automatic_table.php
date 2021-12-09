<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMttlEnergyCardAutomaticTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 能量卡自动购买设置
        Schema::create('mttl_energy_card_automatic', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('user_id')->comment('用户id，关联ti_users');
            $table->tinyInteger('types')->comment('能量卡类型');
            $table->decimal('principal', 12, 4)->comment('本金');
            $table->tinyInteger('total_days')->default(5)->comment('可收益天数');
            $table->decimal('daily_rate', 12, 6)->default(0)->comment('每日收益率');
            $table->boolean('automatic')->default(1)->comment('开启自动购买');

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
        Schema::dropIfExists('mttl_energy_card_automatic');
    }
}
