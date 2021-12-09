<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0)->comment('用户');
            $table->string('symbol', 50)->default('')->comment('币种');
            $table->decimal('num', 20, 10)->default(0.0000000000)->comment('业务操作数量');
            $table->integer('no')->unsigned()->comment('业务编号');
            $table->string('module', 200)->default('')->comment('业务模块');
            $table->string('action', 200)->nullable()->default('')->comment('业务动作');
            $table->json('info')->nullable()->comment('操作信息');
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('action');
            $table->index('no');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coin_log');
    }

}
