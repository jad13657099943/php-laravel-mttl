<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinInternalTradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_internal_trade', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from_user_id')->unsigned()->default(0)->comment('转出方');
            $table->integer('to_user_id')->unsigned()->default(0)->comment('转入方');
            $table->decimal('number', 20, 8)->nullable()->comment('转出金额');
            $table->decimal('get_num', 20, 8)->nullable()->comment('实际得到金额');
            $table->decimal('fee',20, 8)->nullable()->default(0)->comment('手续费');
            $table->string('symbol', 50)->default('')->comment('币种');
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
        Schema::drop('coin_internal_trade');
    }
}
