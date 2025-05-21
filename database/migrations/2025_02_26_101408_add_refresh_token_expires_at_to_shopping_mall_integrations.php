<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('shopping_mall_integrations', function (Blueprint $table) {
        $table->timestamp('refresh_token_expires_at')->nullable()->after('refresh_token')
              ->comment('리프레시 토큰 만료 시간');
    });
}

public function down()
{
    Schema::table('shopping_mall_integrations', function (Blueprint $table) {
        $table->dropColumn('refresh_token_expires_at');
    });
}
};
