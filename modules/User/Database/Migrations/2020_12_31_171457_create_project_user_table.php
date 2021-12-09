<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('show_userid', 20)->unique('show_userid')->comment('展示的用户ID');
            $table->integer('user_id')->unique('user_id')->default(0)->comment('用户ID');
            $table->integer('grade')->default(0)->comment('等级');
            $table->integer('parent_id')->index('parent_id')->default(0)->comment('推荐人，冗余');
            $table->tinyInteger('type')->default(0)->comment('账户类型（0:平民；1:精神领袖）');
            $table->string('address', 50)->unique('address')->nullable()->comment('钱包地址');
            $table->string('header', 255)->nullable()->comment('头像地址');
            $table->string('nick_name', 20)->nullable()->comment('昵称');
            $table->string('team_mark', 20)->nullable()->comment('团队标识');
            $table->json('authority')->comment('权限');

            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_user');
    }
}
