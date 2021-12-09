<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinExchangeMarketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coin_pay', 20)->nullable()->comment('兑出币种');
            $table->string('coin_get', 20)->nullable()->comment('获得币种');
            $table->float('cost_ratio', 10, 1)->default(0)->nullable()->comment('兑换费费用汇率,千分比');
            $table->decimal('pay_amount_min', 10, 4)->default(0)->nullable()->comment('最小兑出币种数量');
            $table->decimal('ratio_max', 10, 4)->default(0)->nullable()->comment('最大兑换比例');
            $table->boolean('status')->default(0)->comment('状态 0=未启用 1=已启用');
            $table->decimal('ratio',10,4)->comment('兑换比例');
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();
        });

        Schema::create('exchange_order', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('用户id号');
            $table->string('coin_pay', 20)->nullable()->comment('支出币种');
            $table->string('coin_get', 20)->nullable()->comment('获得币种');
            $table->decimal('pay_amount', 10, 4)->nullable()->comment('支出数量');
            $table->decimal('get_amount', 10, 4)->nullable()->comment('兑换获得数量');
            $table->decimal('cost', 10, 4)->nullable()->comment('兑换手续费');
            $table->boolean('status')->default(0)->comment('状态-1=兑换失败,0=未兑换1=兑换成功');
            $table->integer('use_reward')->nullable()->default(0)->comment('是否参与计算了星级奖励');
            $table->integer('user_grade')->nullable()->default(0)->comment('会员星级');
            $table->float('cost_rate')->nullable()->default(0)->comment('手续费比例');
            $table->dateTimeTz('ratio_at')->nullable()->comment('比例的核算时间');
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
        Schema::drop('exchange_settings');
        Schema::drop('exchange_order');
    }
}
