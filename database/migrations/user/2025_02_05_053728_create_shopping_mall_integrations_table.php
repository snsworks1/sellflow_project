<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingMallIntegrationsTable extends Migration
{
    public function up()
{
    Schema::connection('dynamic')->create('shopping_mall_integrations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // 연동한 사용자 ID
        $table->string('mall_id'); // 쇼핑몰 도메인 (ex: mymall.cafe24.com)
        $table->enum('platform', ['cafe24', 'smartstore', 'coupang', 'etc']); // 플랫폼 구분
        $table->string('client_id')->nullable(); // OAuth Client ID
        $table->string('client_secret')->nullable(); // OAuth Client Secret
        $table->text('access_token')->nullable(); // API Access Token
        $table->text('refresh_token')->nullable(); // API Refresh Token
        $table->timestamp('refresh_token_expires_at')->nullable(); // API Refresh Token
        $table->timestamp('expires_at')->nullable(); // Access Token 만료 시간
        // ✅ 새로운 쇼핑몰별 필수 정보 추가
        $table->string('vendor_id')->nullable(); // 쿠팡 Vendor ID
        $table->string('access_key')->nullable(); // 쿠팡 API Access Key
        $table->string('secret_key')->nullable(); // 쿠팡 API Secret Key

        $table->timestamps();

        $table->unique(['user_id', 'mall_id', 'platform']); // 동일한 사용자-쇼핑몰-플랫폼 중복 방지
    });
}

    public function down()
    {
        Schema::connection('dynamic')->dropIfExists('shopping_mall_integrations'); // ✅ tenant → dynamic 변경
    }
}
