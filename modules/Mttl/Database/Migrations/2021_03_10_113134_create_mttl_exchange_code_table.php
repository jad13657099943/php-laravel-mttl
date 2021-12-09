<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMttlExchangeCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mttl_exchange_code', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code', 50)->comment('兑换码');
            $table->decimal('amount', 12, 4)->comment('金额');
            $table->json('user_ids')->nullable()->comment('可兑换的用户id');
            $table->bigInteger('counts')->default(0)->comment('总可兑换次数');
            $table->bigInteger('quota')->default(0)->comment('每人可兑换的次数');
            $table->dateTime('effective_time')->comment('生效时间');
            $table->dateTime('expiration_time')->comment('失效时间');
            $table->tinyInteger('state')->default(1)->comment('状态');
            $table->bigInteger('received_count')->default(0)->comment('已兑换次数');
            $table->bigInteger('received_users')->default(0)->comment('已兑换人数');

            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mttl_exchange_code');
    }
}
