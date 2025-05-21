<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateTemporaryTablesForExistingUsers extends Command
{
    protected $signature = 'temporary-tables:update';
    protected $description = 'Create or update temporary product tables for all existing user databases';

    public function handle()
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $dbName = "sellflow_global_{$user->id}";
            
            // 동적 데이터베이스 설정 및 연결 초기화
            config(['database.connections.dynamic.database' => $dbName]);
            DB::purge('dynamic');

            try {
                if (Schema::connection('dynamic')->hasTable('shopping_mall_products_temp')) {
                    // 기존 테이블의 status 열 업데이트
                    DB::connection('dynamic')->statement(
                        "ALTER TABLE `shopping_mall_products_temp` 
                        CHANGE `status` `status` ENUM('판매중', '품절', '중지', '예약판매', '임시저장', '제외') DEFAULT '임시저장'"
                    );
                    $this->info("Temporary table updated for user ID: {$user->id}");
                } else {
                    // 새 테이블 생성
                    Schema::connection('dynamic')->create('shopping_mall_products_temp', function (Blueprint $table) {
                        $table->id();
                        $table->enum('shop_type', ['Cafe24', 'SmartStore', 'Coupang', 'ESMPlus']);
                        $table->string('shop_account');
                        $table->string('product_id')->unique();
                        $table->string('product_code')->nullable();
                        $table->string('product_name');
                        $table->string('category')->nullable();
                        $table->decimal('price', 10, 2)->nullable();
                        $table->decimal('original_price', 10, 2)->nullable();
                        $table->integer('stock')->default(0);
                        $table->string('main_image_url')->nullable();
                        $table->string('model_name')->nullable();
                        $table->string('supplier_name')->nullable();
                        $table->enum('status', ['판매중', '품절', '중지', '예약판매', '임시저장', '제외'])->default('임시저장');
                        $table->decimal('supply_price', 10, 2)->nullable();
                        $table->boolean('adult_certification')->default(false);
                        $table->string('option_name')->nullable();
                        $table->string('manufacturer')->nullable();
                        $table->string('brand')->nullable();
                        $table->timestamps();
                    });
                    $this->info("Temporary table created for user ID: {$user->id}");
                }
            } catch (\Exception $e) {
                $this->error("Failed for user ID: {$user->id}. Error: " . $e->getMessage());
            }
        }
    }
}
