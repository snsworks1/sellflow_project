<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('oauth_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('mall_id')->unique(); // 쇼핑몰 ID (중복 방지)
            $table->string('platform'); // 쇼핑몰 종류 (Cafe24, SmartStore 등)
            $table->text('access_token'); // OAuth 액세스 토큰
            $table->text('refresh_token')->nullable(); // 리프레시 토큰 (선택)
            $table->timestamp('expires_at')->nullable(); // 액세스 토큰 만료 시간
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('oauth_integrations');
    }
};
