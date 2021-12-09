<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMttlRewardLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 项目奖励记录表
        Schema::create('mttl_reward_log', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('user_id')->comment('用户id，关联ti_users');
            $table->bigInteger('no')->comment('业务编号');
            $table->string('symbol', 50)->comment('币种');
            $table->decimal('amount', 12, 4)->comment('金额');
            $table->string('type', 50)->comment('类型');
            $table->string('msg', 200)->comment('描述');
            $table->tinyInteger('algebra')->nullable()->comment('代数');
            $table->bigInteger('source_user')->nullable()->comment('来源用户');
            $table->string('source_show_userid', 20)->nullable()->comment('来源用户id');
            $table->json('extra')->nullable()->comment('附加信息');

            $table->dateTimeTz('created_at');
            $table->dateTimeTz('updated_at');

            $table->index('user_id');
            $table->index('no');
            $table->index('type');
            $table->index('source_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mttl_reward_log');
    }
}
