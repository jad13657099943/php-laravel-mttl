<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('symbol', 50)->nullable()->comment('币种');
            $table->string('chain', 50)->nullable()->comment('主链');
            $table->string('agreement',50)->nullable()->comment('协议名称');
            $table->integer('tokenio_version')->default(1)->nullable()->comment('对应版本1=v1，2=v2');
            $table->boolean('recharge_state')->default(1)->nullable()->comment('状态 0=未启用 1=已启用');
            $table->boolean('withdraw_state')->default(1)->comment('状态 0=未启用 1=已启用');
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coin_config');
    }
}
