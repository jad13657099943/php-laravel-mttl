<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtcCoinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otc_coins', function (Blueprint $table) {
            $table->increments('id')->comment('交易币种设置表');
            $table->string('name', 20)->nullable()->default('')->comment('显示名称');
            $table->string('coin', 20)->nullable()->default('')->comment('币种');
            $table->tinyInteger('buy')->nullable()->default(\Modules\Otc\Models\OtcCoin::BUY_ENABLE)->comment('是否可买');
            $table->tinyInteger('sell')->nullable()->default(\Modules\Otc\Models\OtcCoin::SELL_ENABLE)->comment('是否可卖');
            $table->unsignedDecimal('min', 10, 4)->nullable()->default(1)->comment('单笔最小交易量');
            $table->unsignedDecimal('max', 10, 4)->nullable()->default(1)->comment('单笔最大交易量');
            $table->unsignedDecimal('price_cny', 10, 4)->nullable()->comment('人民币参考价格,=0则取接口实时价');
            $table->tinyInteger('is_enable')->nullable()->default(\Modules\Otc\Models\OtcCoin::ENABLE)->comment('是否启用');
            $table->tinyInteger('sort')->default(10)->comment('排序字段,asc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otc_coins');
    }
}
