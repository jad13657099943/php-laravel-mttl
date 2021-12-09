<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinTradeTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_trade', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->unsigned()->nullable()->default(0);
            $table->string('from')->default('')->comment('真实的转出地址');
            $table->string('to')->default('')->comment('转入地址');
            $table->decimal('num', 20, 8)->comment('数量');
            $table->decimal('gas', 20, 8)->nullable()->comment('转账需要的gas');
            $table->decimal('gas_price', 20, 8)->nullable()->comment('转账设置的gas价格');
            $table->string('memo', 50)->nullable()->default('')->comment('EOS类转账.备注的用户唯一ID');
            $table->string('symbol', 20)->default('')->comment('币种');
            $table->string('chain', 20)->default('')->comment('链');
            $table->boolean('tokenio_version')->nullable()->default(1)->comment('由哪个版本生成的1或2');
            $table->integer('no')->unsigned()->default(0)->comment('业务编号');
            $table->string('module', 20)->default('')->comment('业务模块');
            $table->string('action', 200)->nullable()->comment('业务动作');
            $table->string('hash', 200)->nullable()->default('')->comment('对应的链上哈希值');
            $table->integer('state')->comment('-4=余额不足，-3=被取消、-2=人工转账、-1=已确认待处理【处理中】0广播完成.已生成哈希值【处理中】，1已打包【已打包】，2已完成.打包不可逆【已完成】');
            $table->boolean('weight')->default(0)->comment('处理权重 withdraw时=100 gas时=15 pooling_cold时=10 其他值时=50');
            $table->dateTimeTz('withdraw_at')->nullable()->comment('最后一次想tokenio提交请求的时间');
            $table->integer('block_number')->nullable()->comment('块高度');
            $table->string('state_info')->nullable()->comment('状态说明.如：余额不足，被安全风控，黑名单等');
            $table->dateTimeTz('packaged_at')->nullable()->comment('打包时间');
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();

            $table->index(['user_id', 'module', 'action', 'no'], 'user_module_action_no');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coin_trade');
    }

}
