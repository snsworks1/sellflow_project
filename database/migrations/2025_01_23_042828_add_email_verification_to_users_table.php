<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailVerificationToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_email_verified')->default(false)->after('email');
            $table->string('email_verification_token', 255)->nullable()->after('is_email_verified');
            $table->timestamp('token_created_at')->nullable()->after('email_verification_token');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_email_verified', 'email_verification_token', 'token_created_at']);
        });
    }
}