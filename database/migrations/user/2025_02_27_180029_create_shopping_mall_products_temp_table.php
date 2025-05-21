<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 임시 상품 테이블 생성
     */
    public function up()
    {
        Schema::create('shopping_mall_products_temp', function (Blueprint $table) {
            $table->id();
            $table->enum('shop_type', ['Cafe24', 'SmartStore', 'Coupang', 'ESMPlus']);
            $table->string('shop_account');
            $table->string('product_id')->unique();
            $table->string('product_code')->nullable();
            $table->string('product_name');
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('original_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('main_image_url')->nullable();
            $table->string('model_name')->nullable();
            $table->string('supplier_name')->nullable();
            $table->enum('status', ['판매중', '품절', '중지', '예약판매', '임시저장'])->default('임시저장');
            $table->decimal('supply_price', 10, 2)->nullable();
            $table->boolean('adult_certification')->default(false);
            $table->string('option_name')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('brand')->nullable();
            $table->timestamps();
        });
    }

    /**
     * 임시 상품 테이블 삭제
     */
    public function down()
    {
        Schema::dropIfExists('shopping_mall_products_temp');
    }
};
