<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinTokenioNoticeTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_tokenio_notice', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string('type')->default('')->comment('通知事件类型');
            $table->string('nonce')->default('')->comment('事件对象编号');
            $table->json('data')->comment('数据(JSON串)');
            $table->boolean('state')->default(0)->comment('-1不需处理0=待处理，1=已处理');
            $table->smallInteger('version')->default(0)->comment('1=V1,2=V2');
            $table->unique(['type', 'nonce'], 'type_nonce');
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
        Schema::drop('coin_tokenio_notice');
    }

}
