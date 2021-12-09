<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinChainTradeTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_chain_trade', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->string('symbol', 20)->default('')->comment('币种');
            $table->string('from', 200)->comment('真实的转出地址');
            $table->string('to', 200)->default('')->comment('转入地址');
            $table->string('memo', 100)->nullable()->comment('EOS类转账.备注的用户唯一ID');
            $table->string('type', 20)->default('')->comment('业务模块');
            $table->integer('no')->unsigned()->default(0)->comment('业务编号');
            $table->decimal('num', 20, 8)->comment('数量');
            $table->float('gas_price', 10)->unsigned()->nullable()->comment('转账设置的gas价格');
            $table->string('hash')->nullable()->comment('对应的链上哈希值');
            $table->string('transfer_type',
                20)->default('0')->comment('转账类型 transfer=普通转账 supply=补充coins supply_gas=补充gas');
            $table->boolean('weight')->default(0)->comment('withdraw时=100 gas时=15 pooling_cold时=10 其他值时=50');
            $table->integer('block_number')->unsigned()->nullable()->default(0)->comment('块高度');
            $table->integer('state')->comment('-4=余额不足，-3=被取消、-2=人工转账、-1=已确认待处理【处理中】0广播完成.已生成哈希值【处理中】，1已打包【已打包】，2已完成.打包不可逆【已完成】');
            $table->string('state_info')->nullable()->comment('状态说明.如：余额不足，被安全风控，黑名单等');
            $table->dateTimeTz('withdraw_at')->nullable()->comment('最后一次想tokenio提交请求的时间');
            $table->dateTimeTz('packaged_at')->nullable()->comment('打包时间');
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();
            $table->unique(['type', 'no'], 'module_module_no');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coin_chain_trade');
    }

}
