<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('contact')->nullable()->after('email'); // 연락처 필드 추가
        $table->boolean('alert_agreement')->default(false)->after('contact'); // 알림 수신 동의 필드 추가
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['contact', 'alert_agreement']); // 필드 삭제
    });
}

};
