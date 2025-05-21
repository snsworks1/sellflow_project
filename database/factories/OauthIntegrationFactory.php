<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OauthIntegration;

class OauthIntegrationFactory extends Factory
{
    protected $model = OauthIntegration::class;

    public function definition(): array
    {
        return [
            'user_id' => 1, // 테스트용 사용자 ID
            'mall_id' => $this->faker->uuid,
            'platform' => 'cafe24',
            'client_id' => $this->faker->uuid,
            'client_secret' => $this->faker->uuid,
            'access_token' => $this->faker->sha256,
            'refresh_token' => $this->faker->sha256,
            'expires_at' => now()->addDays(30),
        ];
    }
}
