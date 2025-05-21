<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class MigrateUserDatabases extends Command
{
    protected $signature = 'migrate:user-databases';
    protected $description = 'Run migrations for all user-specific databases';

    public function handle()
    {
        // 기존 사용자별 데이터베이스 조회
        $databases = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'sellflow_global_%'");

        // 배열 변환 후 필드명 정확히 확인
        $databaseNames = array_map(function ($db) {
            return $db->schema_name ?? $db->SCHEMA_NAME ?? null;
        }, $databases);

        // null 값 제거
        $databaseNames = array_filter($databaseNames);

        foreach ($databaseNames as $dbName) {
            // 🚨 동적으로 `dynamic` 연결 변경
            config(['database.connections.dynamic.database' => $dbName]);
            DB::purge('dynamic'); // 기존 연결 초기화

            // 특정 사용자 DB에서 마이그레이션 실행
            Artisan::call('migrate', [
                '--database' => 'dynamic', // ✅ tenant가 아니라 dynamic 사용
                '--path' => 'database/migrations/user', // 마이그레이션 파일이 있는 경로
                '--force' => true,
            ]);

            $this->info("Migration applied to: {$dbName}");
        }
    }
}
