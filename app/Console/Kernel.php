<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// ✅ 사용자 정의 명령어 클래스 추가
use App\Console\Commands\CreateOrUpdateTemporaryTablesForExistingUsers;

class Kernel extends ConsoleKernel
{
    /**
     * Artisan 명령어 정의
     */
    protected $commands = [
        CreateOrUpdateTemporaryTablesForExistingUsers::class,
    ];

    /**
     * 스케줄링 명령어 정의
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('email-verifications:expire')->everyMinute();
    }

    /**
     * Artisan 명령어를 콘솔에 등록
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
