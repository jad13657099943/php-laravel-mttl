<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinAssetTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_asset', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('用户id号');
            $table->string('symbol', 20)->default('')->comment('币种');
            $table->decimal('balance', 20, 10)->unsigned()->default(0.00000000)->comment('余额');
            $table->boolean('status')->default(0)->comment('状态 0=未启用 1=已启用');
            $table->boolean('frozen')->default(0)->comment('冻结 0=未冻结 1=已冻结');
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();

            $table->unique(['user_id', 'symbol'], 'us');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coin_asset');
    }

}
