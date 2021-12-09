<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinWithdrawTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_withdraw', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->nullable();
            $table->string('to')->comment('转入地址');
            $table->string('symbol', 20)->comment('币种');
            $table->string('chain', 20)->default('')->comment('链');
            $table->string('memo', 50)->nullable()->comment('EOS类转账.备注的用户唯一ID');
            $table->decimal('num', 20, 8)->default(0.00000000)->comment('数量');
            $table->decimal('cost', 10,6)->nullable()->comment('扣除手续费');
            $table->integer('state')->nullable()->default(-1)->comment('-3=被撤销【已撤销】、-2=待确认(需人工审核)【待确认】、-1=已确认待处理【处理中】\n0广播完成.已生成哈希值【处理中】，1已打包【已打包】，2已完成.打包不可逆【已完成】');
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
        Schema::drop('coin_withdraw');
    }

}
