<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtcUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otc_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('success_buy')->default(0)->comment('成交笔数，买单');
            $table->unsignedInteger('success_sell')->default(0)->comment('成交笔数，卖单');
            $table->unsignedInteger('average_time')->nullable()->comment('平均确认用时');
            $table->tinyInteger('enable_buy')->default(\Modules\Otc\Models\OtcUser::BUY_ENABLE)->comment('是否可以买,0=不能买，1=可以买');
            $table->tinyInteger('enable_sell')->default(\Modules\Otc\Models\OtcUser::SELL_ENABLE)->comment('是否可以卖，0=不能卖，1=可以卖');
            $table->float('buy_rate', 10, 3)->nullable()->comment('购买汇率，万分比，可为负');
            $table->float('sell_rate', 10, 3)->nullable()->comment('卖出汇率，万分比，可为负');
            $table->float('buy_cost', 10, 3)->nullable()->comment('购买单笔费用');
            $table->float('sell_cost', 10, 3)->nullable()->comment('卖出单笔费用');
            $table->tinyInteger('sell_min')->nullable()->comment('单笔金额限制下限');
            $table->tinyInteger('sell_max')->nullable()->comment('单笔金额限制上限');
            $table->dateTime('last_active_at')->nullable()->comment('最新在线时间');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otc_users');
    }
}
