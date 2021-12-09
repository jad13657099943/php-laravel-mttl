<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserNoticeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notice', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unique('user_id')->default(0)->comment('用户ID');
            $table->integer('state')->default(0)->comment('状态');
            $table->string('type',50)->nullable()->comment('类型');
            $table->json('title')->nullable()->comment('标题');
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
        Schema::dropIfExists('user_notice');
    }
}
