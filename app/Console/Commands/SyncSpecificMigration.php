<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SyncSpecificMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:specific-migration {--migration= : 실행할 마이그레이션 파일명}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '기존 사용자 데이터베이스에 특정 마이그레이션 파일을 적용합니다.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $migrationFile = $this->option('migration');

        if (!$migrationFile) {
            $this->error('마이그레이션 파일명을 --migration 옵션으로 지정해주세요.');
            return 1;
        }

        // 모든 가입된 사용자 목록 가져오기
        $users = User::all();

        foreach ($users as $user) {
            $databaseName = 'sellflow_global_' . $user->id;
            
            // 사용자별 데이터베이스 설정 변경
            config(['database.connections.sellflow.database' => 'sellflow_global_' . $user->id]);
            // 연결 설정을 즉시 반영하도록 설정
DB::purge('sellflow');
DB::reconnect('sellflow');
            $this->info("동기화 시작: $databaseName");

            try {
                // 특정 마이그레이션 파일만 강제 실행
                Artisan::call('migrate', [
                    '--database' => 'sellflow',
                    '--path' => 'database/migrations/user',
                    '--force' => true,
                ]);

                $this->info("동기화 완료: $databaseName");

            } catch (\Exception $e) {
                $this->error("동기화 실패: $databaseName - " . $e->getMessage());
            }
        }

        $this->info("모든 사용자 데이터베이스에 특정 마이그레이션 동기화 완료!");

        return 0;
    }
}
