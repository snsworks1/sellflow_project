<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // users 테이블 참조
            $table->string('business_type'); // 사업자 유형
            $table->string('business_number')->unique(); // 사업자 등록 번호
            $table->string('business_name'); // 사업체 이름
            $table->string('ceo_name'); // 대표자 이름
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_profiles');
    }
}