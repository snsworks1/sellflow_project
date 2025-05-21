<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('oauth_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // 사용자 ID (동적 데이터베이스와 동일하게)
            $table->string('mall_id')->unique(); // 쇼핑몰 ID (중복 방지)
            $table->string('platform'); // 쇼핑몰 종류 (Cafe24, SmartStore 등)

           
            $table->string('client_id')->nullable(); // API Client ID
            $table->string('client_secret')->nullable(); // API Secret Key

            $table->text('access_token'); // OAuth 액세스 토큰
            $table->text('refresh_token')->nullable(); // 리프레시 토큰 (선택)
            
            $table->timestamp('refresh_token_expires_at')->nullable(); // 리프레시 토큰 만료 시간
            $table->timestamp('expires_at')->nullable(); // 액세스 토큰 만료 시간
            $table->string('vendor_id')->nullable(); // 판매자 ID (예: 쿠팡 API용)
            $table->string('access_key')->nullable(); // Access Key (스마트스토어 등)
            $table->string('secret_key')->nullable(); // Secret Key (스마트스토어 등)

            $table->timestamps();

            // 외래 키 및 인덱스 설정
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('oauth_integrations');
    }
};
