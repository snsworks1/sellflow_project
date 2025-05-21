<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeTokenNullableInEmailVerifications extends Migration
{
    public function up()
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->string('token')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->string('token')->nullable(false)->change();
        });
    }
}
