<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinSystemWalletTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_system_wallet', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('chain', 20);
            $table->string('address', 50);
            $table->boolean('tokenio_version')->nullable()->default(1)->comment('由哪个版本生成的1或2');
            $table->boolean('level')->nullable()->default(0)->comment('钱包性质:0冷钱包1温钱包2热钱包');
            $table->string('remark')->nullable()->comment('备注说明');
            $table->decimal('gas_balance', 20, 0)->nullable()->comment('gas余额');
            $table->decimal('notice_max', 20, 8)->nullable()->comment('余额大于此值时通知,0为不通知');
            $table->decimal('notice_min', 20, 8)->nullable()->comment('余额小于此值时通知,0为不通知');
            $table->string('notice')->nullable()->comment('余额通知, 手机号或者邮箱');
            $table->boolean('is_tokenio')->nullable()->default(0)->comment('是否为Tokenio生成钱包');
            $table->string('type', 40)->nullable()->default('')->comment('钱包业务类型:gas=gas转账钱包');

            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();

            $table->unique(['chain', 'address'], 'wallet_unique');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coin_system_wallet');
    }

}
