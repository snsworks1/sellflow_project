<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OauthIntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::connection('sellflow')->table('oauth_integrations')->insert([
            [
                'client_id' => Str::random(32),
                'client_secret' => Str::random(64),
                'access_token' => Str::random(60),
                'refresh_token' => Str::random(60),
                'expires_at' => Carbon::now()->addDays(30), // 30일 후 만료
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
