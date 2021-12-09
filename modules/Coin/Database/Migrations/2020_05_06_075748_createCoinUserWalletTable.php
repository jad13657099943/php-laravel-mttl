<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinUserWalletTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_user_wallet', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->integer('tokenio_version')->unsigned()->default(1);
            $table->string('chain', 50)->default('')->comment('所属链');
            $table->string('address', 200)->default('')->comment('钱包地址');
            $table->dateTimeTz('created_at')->nullable();
            $table->dateTimeTz('updated_at')->nullable();

            $table->unique(['user_id', 'chain', 'address'], 'user_chain_address');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coin_user_wallet');
    }

}
