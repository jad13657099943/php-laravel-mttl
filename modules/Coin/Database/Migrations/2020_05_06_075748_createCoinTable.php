<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCoinTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('chain', 20)->default('')->comment('所属链');
            $table->string('symbol', 20)->default('')->unique('symbol')->comment('资产编号，在本系统显示的名称, 不允许重复, 和id同性质');
            $table->string('coin', 20)->comment('现实中的币种名称, 一个币可以在不同链上有不同的名字, 不同币种不可重复');
            $table->string('short_name', 50)->nullable()->comment('代币的英文简称, 可重复, 用于应用端显示');
            $table->string('full_name')->nullable()->comment('代币的中文或英文全称');
            $table->string('real_symbol', 20)->nullable()->comment('链上真实的symbol名，eos转账用到');
            $table->string('unique')->default('')->comment('此资产在链上的唯一标识.如：ETH的合约地址');
            $table->integer('decimals')->default(8)->comment('合约支持的小数点位数');
            $table->decimal('hot_max', 20,
                10)->unsigned()->nullable()->default(0.0000000000)->comment('小于等于此值时使用热钱包自动转账');
            $table->decimal('withdraw_min', 20, 8)->unsigned()->nullable()->default(1.00000000)->comment('提币最小额度');
            $table->decimal('withdraw_max', 20, 8)->unsigned()->nullable()->default(100.00000000)->comment('提币最大额度');
            $table->boolean('withdraw_state')->default(2)->comment('提现状态：0=暂停提现、1=全部需要人工审核、2=走系统设置');
            $table->decimal('withdraw_fee', 20, 8)->unsigned()->default(0.00000000)->comment('提现费用,单位为该symbol');
            $table->decimal('recharge_min', 20,
                8)->unsigned()->nullable()->default(0.00010000)->comment('充值入账最小金额(建议值)');
            $table->boolean('recharge_state')->default(1)->comment('充值状态.1=开启充值、0=暂停充值');
            $table->dateTimeTz('state_updated_at')->nullable()->comment('充提状态更新时间');
            $table->boolean('status')->default(0)->comment('状态 0=未启用 1=已启用');
            $table->decimal('cold_min', 20,
                8)->nullable()->default(100.00000000)->comment('收款钱包余额大于此值时.自动转入冷钱包,=-1则表示不需要转');
            $table->text('comment')->nullable()->comment('通证的注释说明，包括通证的发行方及通证的作用等介绍');
            $table->string('contract_hash')->nullable()->default('')->comment('合约哈希值');
            $table->boolean('unit_decimals')->nullable()->default(8)->comment('该值作为换算单位显示时的小数点位数');
            $table->boolean('balance_decimals')->nullable()->default(4)->comment('该值作为余额显示时的小数点位数');
            $table->boolean('internal_state')->nullable()->default(1)->comment('是否开启内部转账');
            $table->decimal('gas_price', 10)->unsigned()->nullable()->default(0.00)->comment('旷工费价格');
            $table->string('icon')->nullable()->default('')->comment('币种图标');
            $table->decimal('diff_num', 20, 8)->unsigned()->nullable()->default(0)->comment('两次检测币种余额相差金额即提醒');
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
        Schema::drop('coin');
    }

}
