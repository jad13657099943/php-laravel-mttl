<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMttlEnergyCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 能量卡表
        Schema::create('mttl_energy_card', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->tinyInteger('types')->comment('能量卡类型');
            $table->decimal('principal', 12, 4)->comment('本金');
            $table->json('buyable_level')->comment('可购买的级别');
            $table->tinyInteger('total_days')->default(5)->comment('可收益天数');
            $table->decimal('daily_rate', 12, 6)->default(0)->comment('每日收益率');
            $table->boolean('enable')->default(1)->comment('是否启用');

            $table->dateTimeTz('created_at');
            $table->dateTimeTz('updated_at');
            $table->dateTimeTz('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mttl_energy_card');
    }
}
