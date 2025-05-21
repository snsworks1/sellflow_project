<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('business_info', function (Blueprint $table) {
        $table->string('registration_number')->unique()->after('owner_name'); // 사업자등록번호 필드 추가
    });
}

public function down()
{
    Schema::table('business_info', function (Blueprint $table) {
        $table->dropColumn('registration_number'); // 필드 삭제
    });
}
    
};
