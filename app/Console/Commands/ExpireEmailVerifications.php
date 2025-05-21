<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailVerification;

class ExpireEmailVerifications extends Command
{
    protected $signature = 'email-verifications:expire';
    protected $description = 'Expire email verifications older than a specific time';

    public function handle()
    {
        $expiredVerifications = EmailVerification::where('verified', false)
            ->where('created_at', '<', now()->subMinutes(5)) // 인증 요청 후 5분 경과
            ->update([
                'token' => null,
                'verified' => false,
            ]);

        $this->info("Expired $expiredVerifications email verifications.");
    }
}