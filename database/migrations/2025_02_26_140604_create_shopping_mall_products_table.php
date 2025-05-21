<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingMallProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping_mall_products', function (Blueprint $table) {
            $table->id();
            $table->enum('shop_type', ['Cafe24', 'SmartStore', 'Coupang', 'ESMPlus']);
            $table->string('shop_account');
            $table->string('product_id');
            $table->string('product_code')->nullable();
            $table->string('product_name');
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('original_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('main_image_url')->nullable();
            $table->string('model_name')->nullable();
            $table->string('supplier_name')->nullable();
            $table->enum('status', ['판매중', '품절', '중지', '예약판매'])->default('판매중');
            $table->decimal('supply_price', 10, 2)->nullable();
            $table->boolean('adult_certification')->default(false);
            $table->string('option_name')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('brand')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopping_mall_products');
    }
}
