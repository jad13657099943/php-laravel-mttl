<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleCateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_cate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sort')->default(0)->comment('排序');
            $table->integer('state')->default(0)->comment('状态');
            $table->string('msg', 255)->nullable()->comment('说明');
            $table->json('name')->nullable()->comment('标题');
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
        Schema::dropIfExists('article_cate');
    }
}
