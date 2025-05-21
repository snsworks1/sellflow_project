<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveEmailVerificationFromUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verification_token', 'token_created_at', 'is_email_verified']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_verification_token', 255)->nullable();
            $table->timestamp('token_created_at')->nullable();
            $table->boolean('is_email_verified')->default(false);
        });
    }
}
