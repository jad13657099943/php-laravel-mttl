<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinLogModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_log_modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('module')->default('')->comment('模块标识');
            $table->string('action')->default('')->comment('操作标识');
            //$table->string('title')->default('');
            $table->json('title');
            $table->text('remark')->nullable();
            $table->unsignedTinyInteger('enable')->default(\Modules\Coin\Models\CoinLogModules::MODULE_ENABLE)->comment('1-开启，0-禁用');
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
        Schema::drop('coin_log_modules');
    }

}
