<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAppealTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_appeal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment('用户ID');
            $table->integer('state')->default(0)->comment('状态');
            $table->integer('type')->default(0)->comment('类型');
            $table->string('message', 255)->nullable()->comment('工单信息');
            $table->string('reply', 255)->nullable()->comment('回复');
            $table->json('images')->nullable()->comment('图片');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_appeal');
    }
}
