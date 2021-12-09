<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cate_id')->default(0)->comment('分类id');
            $table->integer('state')->default(0)->comment('状态');
            $table->integer('sort')->default(0)->comment('排序');
            $table->json('label')->nullable()->comment('标签');
            $table->json('title')->nullable()->comment('标题');
            $table->json('content')->nullable()->comment('详情');
            $table->json('image')->nullable()->comment('小图片');
            $table->json('desc')->nullable()->comment('简述');
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
        Schema::dropIfExists('article');
    }
}
