<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinRechargeTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_recharge', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->string('symbol', 20)->default('')->comment('币种');
            $table->string('chain', 20)->default('')->comment('链');
            $table->string('from', 200)->default('')->comment('真实的转出地址');
            $table->string('to', 200)->default('')->comment('转入地址');
            $table->decimal('value', 20, 8)->unsigned()->comment('数量');
            $table->string('memo', 100)->nullable()->default('')->comment('EOS类转账.备注的用户唯一ID');
            $table->string('hash')->nullable()->default('')->unique('hash')->comment('对应的链上哈希值');
            $table->boolean('state')->nullable()->default(0)->comment('-3=被撤销【已撤销】、-2=待确认(需人工审核)【待确认】、-1=已确认待处理【处理中】0广播完成.已生成哈希值【处理中】，1已打包【已打包】，2已完成.打包不可逆【已完成】');
            $table->integer('block_number')->unsigned()->nullable()->default(0)->comment('块高度');
            $table->dateTimeTz('packaged_at')->nullable()->comment('打包时间');
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
        Schema::drop('coin_recharge');
    }

}
