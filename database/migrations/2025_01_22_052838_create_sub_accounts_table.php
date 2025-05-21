<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('sub_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained('users')->onDelete('cascade'); // 마스터 계정
            $table->foreignId('sub_id')->constrained('users')->onDelete('cascade'); // 서브 계정
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_accounts');
    }
}