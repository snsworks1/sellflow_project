<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 사용자 참조
            $table->foreignId('service_id')->constrained()->onDelete('cascade'); // 서비스 참조
            $table->decimal('amount', 10, 2); // 결제 금액
            $table->date('start_date'); // 서비스 시작일
            $table->date('end_date'); // 서비스 종료일
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active'); // 상태
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}