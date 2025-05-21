<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cafe24_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('mall_id'); // 쇼핑몰 ID
            $table->string('event_type'); // Webhook 이벤트 유형
            $table->json('payload'); // Webhook에서 받은 데이터 전체 저장
            $table->timestamp('received_at')->useCurrent(); // 이벤트 수신 시간
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cafe24_webhooks');
    }
};
