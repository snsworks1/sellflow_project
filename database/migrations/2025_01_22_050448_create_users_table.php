<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 사용자 이름
            $table->string('email')->unique(); // 이메일
            $table->string('password'); // 비밀번호
            $table->enum('role', ['master', 'sub'])->default('master'); // 계정 유형
            $table->enum('account_type', ['business', 'personal']); // 회원 유형
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}