<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMttlUserGradeRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mttl_user_grade_record', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('user_id')->comment('用户id');
            $table->tinyInteger('type')->default(1)->comment('1为升级，2为降级');
            $table->tinyInteger('old')->comment('原等级');
            $table->tinyInteger('new')->comment('新等级');
            $table->json('exclude_userid')->comment('排除的用户id');
            $table->tinyInteger('operation')->default(1)->comment('1为自动升级，2为后台操作');

            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->index('user_id');
            $table->index('type');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mttl_user_grade_record');
    }
}
