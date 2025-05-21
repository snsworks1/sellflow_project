<?php

namespace App\Services;

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
        \Log::info('requestWithToken 메서드 호출됨');
    
        if (!is_object($mall)) {
            \Log::error('Mall 데이터가 객체가 아닙니다. 올바른 객체를 전달해야 합니다.');
            return [];
        }
    
        $accessToken = $mall->access_token;
        $expiresAt = Carbon::parse($mall->expires_at);
        $refreshToken = $mall->refresh_token;
        $refreshTokenExpiresAt = Carbon::parse($mall->refresh_token_expires_at);
    
        \Log::info('Access Token 만료 시간: ' . $expiresAt);
        \Log::info('Refresh Token 만료 시간: ' . $refreshTokenExpiresAt);
    
// 엑세스 토큰 만료 시 자동 갱신
if (Carbon::now()->greaterThanOrEqualTo($expiresAt)) {
    \Log::info('Access Token 만료됨. Refresh Token을 사용하여 갱신 시도.');

    if (Carbon::now()->greaterThanOrEqualTo($refreshTokenExpiresAt)) {
        \Log::error('Refresh Token도 만료되었습니다.');
        return [];
    }

    // ✅ $mall 데이터를 배열로 변환
    $mallArray = (array) $mall;
    $mallId = $mallArray['mall_id'] ?? null;
    $userId = $mallArray['user_id'] ?? null;

    if (!$mallId) {
        \Log::error('올바른 mall_id를 찾을 수 없습니다.');
        return [];
    }

    $accessToken = $this->refreshAccessToken($mallId, $refreshToken, $userId);
    
    if (!$accessToken) {
        \Log::error('Access Token 갱신 실패.');
        return [];
    }
}
Log::info('전송할 데이터: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

        \Log::info('유효한 Access Token 사용: ' . $accessToken);
    
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
            'X-Cafe24-Api-Version' => '2024-12-01', // ✅ 올바른 API 버전 추가
        ];
    
        // ✅ URL 경로 수정: api/v2 + admin/products
        $url = "https://{$mall->mall_id}.cafe24api.com/api/v2/admin/{$endpoint}";
    
        return $this->makeRequest($method, $url, $params, $headers);
    }
    



    /**
     * Cafe24 엑세스 토큰을 갱신합니다.
     */


    // Cafe24 엑세스 토큰을 갱신합니다.
public function refreshAccessToken(string $mallId, string $refreshToken, int $userId): array
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


     

/**
 * Cafe24 API를 통해 상품 데이터를 수집합니다.
 */
public function fetchProducts(string $mallId, string $accessToken, array $params = []): array
{
    try {
        $endpoint = "https://{$mallId}.cafe24api.com/api/v2/admin/products";
        $allProducts = [];
        $perPage = 100;
        $offset = 0;
        $totalProductCount = null;

        do {
            $params['limit'] = $perPage;
            $params['offset'] = $offset;
            $params['sort'] = 'created_date';
            $params['order'] = 'desc'; // 최신 데이터부터 가져오기

            \Log::info("API 호출 - offset: {$offset}");

            $headers = [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
                'X-Cafe24-Api-Version' => '2024-12-01',
            ];

            $response = Http::withHeaders($headers)->get($endpoint, $params);

            if ($response->failed()) {
                \Log::error('상품 수집 실패: ' . $response->body());
                break;
            }

            $responseData = $response->json();

            if (!isset($responseData['products']) || empty($responseData['products'])) {
                \Log::warning('API 응답 데이터가 비어 있습니다.');
                break;
            }

            $products = $responseData['products'];
            $allProducts = array_merge($allProducts, $products);
            \Log::info('현재 수집된 상품 개수: ' . count($allProducts));

            $offset += $perPage;

            usleep(500000); // 0.5초 대기

        } while (true);

        return ['success' => true, 'products' => $allProducts];

    } catch (\Exception $e) {
        \Log::error('상품 수집 중 오류 발생: ' . $e->getMessage());
        return ['success' => false, 'message' => '상품 수집 중 오류 발생: ' . $e->getMessage()];
    }
}



private function isAccessTokenExpired($mallId): bool
{
    $oauthData = OauthIntegration::where('mall_id', $mallId)->first();

    if (!$oauthData) {
        \Log::error("OAuth 데이터 없음: Mall ID = $mallId");
        return true;
    }

    $expiresAt = Carbon::parse($oauthData->expires_at);
    return Carbon::now()->greaterThanOrEqualTo($expiresAt);
}





public function getProducts($mallId, $dateRange)
{
    Log::info("Cafe24 상품 데이터 요청: Mall ID = $mallId");

    // ✅ Access Token 만료 여부 확인 및 갱신
    if ($this->isAccessTokenExpired($mallId)) {
        Log::info("Access Token이 만료됨. Refresh Token으로 갱신 시도...");
        $this->refreshAccessToken($mallId);
    }

    // ✅ Access Token 가져오기
    $accessToken = $this->getAccessToken($mallId);
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
