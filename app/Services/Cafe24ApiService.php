<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\OauthIntegration;
use Carbon\Carbon;


class Cafe24ApiService
{
    /**
     * 엑세스 토큰을 사용하여 API 요청을 수행합니다.
     * 만료 시간이 임박했을 경우 자동으로 토큰을 갱신합니다.
     */
    
     public function requestWithToken($mall, $endpoint, $params = [], $method = 'GET')
     {
         \Log::info('📌 requestWithToken() 호출');
     
         // 객체 검사
         if (!is_object($mall)) {
             \Log::error('❌ Mall 객체가 아닙니다.');
             return ['success' => false, 'message' => '잘못된 mall 객체'];
         }
     
         $accessToken = $mall->access_token;
         $expiresAt = Carbon::parse($mall->expires_at);
         $refreshToken = $mall->refresh_token;
         $refreshTokenExpiresAt = Carbon::parse($mall->refresh_token_expires_at);
     
         $mallId = $mall->mall_id ?? null;
         $userId = $mall->user_id ?? null;
     
         if (!$mallId || !$userId) {
             \Log::error('❌ mall_id 또는 user_id 없음');
             return ['success' => false, 'message' => 'mall_id 또는 user_id 없음'];
         }
     
         // ✅ Access Token 만료 확인
         if (Carbon::now()->greaterThanOrEqualTo($expiresAt)) {
             \Log::warning("🔄 Access Token 만료. 갱신 시도: {$mallId}");
     
             if (Carbon::now()->greaterThanOrEqualTo($refreshTokenExpiresAt)) {
                 \Log::error("❌ Refresh Token도 만료됨. 재연동 필요");
                 return ['success' => false, 'message' => 'Refresh Token 만료. 재인증 필요'];
             }
     
             $tokenResult = $this->refreshAccessToken($mallId, $refreshToken, $userId);
     
             if (!isset($tokenResult['success']) || !$tokenResult['success']) {
                 \Log::error("❌ Access Token 갱신 실패");
                 return ['success' => false, 'message' => 'Access Token 갱신 실패'];
             }
     
             $accessToken = $tokenResult['access_token'];
             \Log::info("✅ 새 Access Token 사용: {$accessToken}");
         }
     
         // ✅ API 요청 실행
         $headers = [
             'Authorization' => 'Bearer ' . $accessToken,
             'Content-Type' => 'application/json',
             'X-Cafe24-Api-Version' => '2024-12-01',
         ];
     
         $url = "https://{$mallId}.cafe24api.com/api/v2/admin/{$endpoint}";
         $response = $this->makeRequest($method, $url, $params, $headers);
     
         // ✅ 401 실패 시 재시도 로직
         if (
             is_array($response) &&
             isset($response['success']) &&
             !$response['success'] &&
             isset($response['message']) &&
             str_contains($response['message'], '401')
         ) {
             \Log::warning("⚠️ 첫 요청 실패(401). 최종 재시도 시작...");
     
             $tokenResult = $this->refreshAccessToken($mallId, $refreshToken, $userId);
     
             if (isset($tokenResult['success']) && $tokenResult['success']) {
                 $headers['Authorization'] = 'Bearer ' . $tokenResult['access_token'];
                 return $this->makeRequest($method, $url, $params, $headers);
             }
     
             return ['success' => false, 'message' => '재시도 중 Access Token 갱신 실패'];
         }
     
         // ✅ ✅ ✅ 이 줄이 없으면 정상 요청 후에도 반환값 없이 끝남
         return $response;
     }
     


    /**
     * Cafe24 엑세스 토큰을 갱신합니다.
     */


    // Cafe24 엑세스 토큰을 갱신합니다.
    public function refreshAccessToken(string $mallId, string $refreshToken, int $userId)
    {
    $clientId = env('CAFE24_CLIENT_ID');
    $clientSecret = env('CAFE24_CLIENT_SECRET');

    \Log::info("엑세스 토큰 갱신 시도: Mall ID = {$mallId}");
    \Log::info("사용할 Client ID: " . $clientId);
    \Log::info("사용할 Client Secret: " . $clientSecret);

    try {
        $authorizationHeader = 'Basic ' . base64_encode("{$clientId}:{$clientSecret}");
        \Log::info("Authorization 헤더: " . $authorizationHeader);

        $response = Http::asForm()->withHeaders([
            'Authorization' => $authorizationHeader,
        ])->post("https://{$mallId}.cafe24api.com/api/v2/oauth/token", [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        \Log::info('Cafe24 응답 데이터: ' . $response->body());

        if ($response->failed()) {
            \Log::error('토큰 갱신 실패 응답: ' . $response->body());
            return ['success' => false, 'message' => '토큰 갱신 실패: ' . $response->body()];
        }

        $tokenData = $response->json();

        if (!isset($tokenData['access_token']) || !isset($tokenData['refresh_token'])) {
            return ['success' => false, 'message' => '새로운 액세스 토큰 또는 리프레시 토큰을 가져올 수 없습니다.'];
        }

        // ✅ expires_at 값을 올바르게 설정
        $expiresAt = Carbon::parse($tokenData['expires_at'] ?? 'now')->format('Y-m-d H:i:s');
        $refreshExpiresAt = Carbon::parse($tokenData['refresh_token_expires_at'] ?? 'now')->format('Y-m-d H:i:s');

        \Log::info("새로운 Access Token 만료 시간: {$expiresAt}");
        \Log::info("새로운 Refresh Token 만료 시간: {$refreshExpiresAt}");

        // ✅ 메인 테이블 업데이트
        OauthIntegration::where('mall_id', $mallId)->update([
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'refresh_token_expires_at' => $refreshExpiresAt,
            'expires_at' => $expiresAt,
            'updated_at' => now(),
        ]);

        // ✅ 사용자별 동적 DB 업데이트
        DB::table("sellflow_global_{$userId}.shopping_mall_integrations")
            ->where('mall_id', $mallId)
            ->update([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'refresh_token_expires_at' => $refreshExpiresAt,
                'expires_at' => $expiresAt,
                'updated_at' => now(),
            ]);

        return [
            'success' => true,
            'access_token' => $tokenData['access_token'],
            'expires_at' => $expiresAt,
        ];

    } catch (\Exception $e) {
        \Log::error('토큰 갱신 중 오류 발생: ' . $e->getMessage());
        return ['success' => false, 'message' => '토큰 갱신 중 오류 발생: ' . $e->getMessage()];
    }
}






private function isAccessTokenExpired($mallId): bool
{
    $oauthData = OauthIntegration::where('mall_id', $mallId)->first();

    if (!$oauthData) {
        \Log::error("OAuth 데이터 없음: Mall ID = " . json_encode($mallId, JSON_UNESCAPED_UNICODE));
        return true;
    }

    $expiresAt = Carbon::parse($oauthData->expires_at);
    return Carbon::now()->greaterThanOrEqualTo($expiresAt);
}





public function getProducts($mall, $dateRange) // <-- 매개변수 명확히 $mall
{
    $mallId = is_array($mall) ? ($mall['mall_id'] ?? null) : ($mall->mall_id ?? null);
    $userId = is_array($mall) ? ($mall['user_id'] ?? null) : ($mall->user_id ?? null);
    $refreshToken = is_array($mall) ? ($mall['refresh_token'] ?? null) : ($mall->refresh_token ?? null);

    if (!is_string($mallId)) {
        Log::warning('⚠️ mallId가 문자열이 아닙니다: ' . json_encode($mall));
        return ['success' => false, 'message' => 'mallId 형식 오류'];
    }

    Log::info("Cafe24 상품 데이터 요청: Mall ID = {$mallId}");

    // ✅ Access Token 만료 여부 확인 및 갱신
    if ($this->isAccessTokenExpired($mallId)) {
        Log::info("Access Token이 만료됨. Refresh Token으로 갱신 시도...");
        $this->refreshAccessToken($mallId, $refreshToken, $userId);
    }

    // ✅ Access Token 가져오기
    $accessToken = is_array($mall) ? ($mall['access_token'] ?? null) : ($mall->access_token ?? null);
    Log::info("사용할 Access Token: " . $accessToken);

    // ✅ API 요청 URL
    $url = "https://{$mallId}.cafe24api.com/api/v2/admin/products";

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type' => 'application/json',
    ])->get($url, [
        'created_start_date' => $dateRange['created_start_date'],
        'created_end_date' => $dateRange['created_end_date'],
        'limit' => 100
    ]);

    // ✅ API 응답 확인
    if (!$response->successful()) {
        Log::error("상품 데이터 요청 실패: ", ['response' => $response->json()]);
        return ['success' => false, 'message' => '상품 데이터를 가져올 수 없습니다.'];
    }

    return $response->json();
}






public function makeRequest(string $method, string $url, array $params = [], array $headers = [])
{
    try {
        \Log::info("HTTP 요청 시작: {$method} {$url}");
        \Log::info("요청 파라미터: ", $params);
        \Log::info("요청 헤더: ", $headers);

        $response = match (strtoupper($method)) {
            'POST' => Http::withHeaders($headers)->post($url, $params),
            'PUT' => Http::withHeaders($headers)->put($url, $params),
            'DELETE' => Http::withHeaders($headers)->delete($url, $params),
            default => Http::withHeaders($headers)->get($url, $params),
        };

        if ($response->failed()) {
            \Log::error('API 요청 실패: ' . $response->body());
            return ['success' => false, 'message' => 'API 요청 실패: ' . $response->body()];
        }

        \Log::info('API 응답 데이터: ' . $response->body());

        return $response->json();
        
    } catch (\Exception $e) {
        \Log::error('API 요청 중 오류 발생: ' . $e->getMessage());
        return ['success' => false, 'message' => 'API 요청 중 오류 발생: ' . $e->getMessage()];
    }
}

   
}
