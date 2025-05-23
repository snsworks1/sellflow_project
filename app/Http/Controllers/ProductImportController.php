<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Services\Cafe24ApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;



class ProductImportController extends Controller
{
    protected $cafe24ApiService;

    public function __construct(Cafe24ApiService $cafe24ApiService)
    {
        $this->cafe24ApiService = $cafe24ApiService;
    }

    /**
     * 쇼핑몰 상품 수집 페이지 표시
     */
    public function showImportPage()
    {
        return view('products.import');
    }

    /**
     * 쇼핑몰 상품 수집 처리
     */

     public function importProducts(Request $request)
     {
         $shopType = $request->input('shop_type');
         $shopAccount = $request->input('shop_account');
         $dateRange = $request->input('date_range');
     
         $startDate = Carbon::now()->subDay()->format('Y-m-d');
         $endDate = Carbon::now()->format('Y-m-d');
     
         if ($dateRange !== 'all') {
             switch ($dateRange) {
                 case '1d': $startDate = Carbon::now()->subDay()->format('Y-m-d'); break;
                 case '3d': $startDate = Carbon::now()->subDays(3)->format('Y-m-d'); break;
                 case '7d': $startDate = Carbon::now()->subDays(7)->format('Y-m-d'); break;
                 case '1m': $startDate = Carbon::now()->subMonth()->format('Y-m-d'); break;
                 case '6m': $startDate = Carbon::now()->subMonths(6)->format('Y-m-d'); break;
                 case '1y': $startDate = Carbon::now()->subYear()->format('Y-m-d'); break;
             }
         }
     
         $params = [
             'created_start_date' => $startDate,
             'created_end_date' => $endDate,
             'embed' => 'options',
             'limit' => 100
         ];
     
         Log::info('수집할 상품 등록일 범위:', $params);
     
         $userId = Auth::id();
         $dbName = "sellflow_global_{$userId}";
         config(['database.connections.dynamic.database' => $dbName]);
         DB::purge('dynamic');
     
         $mall = DB::connection('dynamic')
             ->table('shopping_mall_integrations')
             ->where('mall_id', $shopAccount)
             ->where('platform', $shopType)
             ->where('user_id', $userId)
             ->first();
     
         if (!$mall) {
             return response()->json(['success' => false, 'message' => '쇼핑몰 데이터를 찾을 수 없습니다.']);
         }
     
         $mallArray = json_decode(json_encode($mall), true);
     
         $totalProductCount = $this->getTotalProductCount($mallArray, $params);
         Log::info("총 예상 수집 상품 개수: {$totalProductCount}");
     
         if ($totalProductCount > 5000) {
             return response()->json(['success' => false, 'message' => '❗ 5000개를 초과하여 수집할 수 없습니다. 날짜를 조정해주세요.']);
         }
     
         $excludedProductIds = DB::connection('dynamic')
             ->table('shopping_mall_products_temp')
             ->whereIn('status', ['제외', '등록완료'])
             ->pluck('product_id')
             ->toArray();
     
         Log::info('수집 제외할 상품 코드:', $excludedProductIds);
     
         $result = $this->cafe24ApiService->fetchProducts($mallArray['mall_id'], $mallArray['access_token'], $params);
     
         if (!$result['success']) {
             return response()->json(['success' => false, 'message' => '상품 수집 실패']);
         }
     
         $products = collect($result['products'])
             ->unique('product_no')
             ->filter(fn($p) => !in_array($p['product_no'], $excludedProductIds))
             ->values()
             ->all();
     
         Log::info('최종 수집된 상품 개수: ' . count($products));
     
         foreach ($products as $product) {
            $product['option_summary'] = null;

            if (isset($product['options']) && is_array($product['options'])) {
                // ⚠️ 빈 옵션 제외 후, 옵션 문자열 생성
                $filteredOptions = array_filter($product['options'], function ($opt) {
                    return !empty($opt['option_name']) || !empty($opt['option_value']);
                });
        
                $optionStrings = array_map(function ($opt) {
                    $name = trim($opt['option_name'] ?? '');
                    $value = trim($opt['option_value'] ?? '');
                    return "{$name}:{$value}";
                }, $filteredOptions);
        
                $product['option_summary'] = implode(', ', array_filter($optionStrings));
            }
     
             DB::connection('dynamic')->table('shopping_mall_products_temp')->updateOrInsert(
                 ['product_id' => $product['product_no']],
                 [
                     'shop_type' => $shopType,
                     'shop_account' => $shopAccount,
                     'product_code' => $product['product_code'] ?? null,
                     'product_name' => $product['product_name'],
                     'option_name' => $product['option_summary'],
                     'category' => $product['category'] ?? null,
                     'price' => $product['price'] ?? 0,
                     'original_price' => $product['retail_price'] ?? 0,
                     'stock' => $product['stock'] ?? 0,
                     'main_image_url' => $product['detail_image'] ?? null,
                     'model_name' => $product['model_name'] ?? null,
                     'supplier_name' => $product['supplier_name'] ?? null,
                     'status' => '임시저장',
                     'supply_price' => $product['supply_price'] ?? 0,
                     'adult_certification' => $product['adult_certification'] === 'T',
                     'manufacturer' => $product['manufacturer'] ?? null,
                     'brand' => $product['brand_name'] ?? null,
                     'created_at' => now(),
                     'updated_at' => now(),
                 ]
             );
         }
     
         Log::info('임시 테이블에 저장 완료: ' . count($products) . '개');
         return response()->json(['success' => true, 'message' => count($products) . '개의 상품을 임시 테이블에 저장했습니다.']);
     }
     
     
     
    

     public function getAccounts(Request $request)
{
    $shopType = strtolower($request->query('shop_type'));
    \Log::info('getAccounts 메서드 호출됨');
    \Log::info('선택한 shop_type: ' . $shopType);

    $userId = Auth::id();
    $dbName = "sellflow_global_{$userId}";

    config(['database.connections.dynamic.database' => $dbName]);
    DB::purge('dynamic');
    DB::reconnect('dynamic');

    // ✅ Schema 클래스 올바른 사용법
    if (!Schema::connection('dynamic')->hasColumn('shopping_mall_integrations', 'platform')) {
        \Log::error('platform 컬럼을 찾을 수 없습니다.');
        return response()->json(['accounts' => []]);
    }

    try {
        $accounts = DB::connection('dynamic')->table('shopping_mall_integrations')
            ->where('platform', $shopType)
            ->where('user_id', $userId)
            ->pluck('mall_id')
            ->toArray(); // ✅ 배열로 변환

        \Log::info('가져온 계정 목록: ' . json_encode($accounts));

        return response()->json(['accounts' => $accounts]);
    } catch (\Exception $e) {
        \Log::error('계정 가져오기 중 오류: ' . $e->getMessage());
        return response()->json(['accounts' => []], 500);
    }
}


public function getShopTypes(Request $request)
{
    $userId = Auth::id();
    $dbName = "sellflow_global_{$userId}";

    config(['database.connections.dynamic.database' => $dbName]);
    DB::purge('dynamic');

    // ✅ 잘못된 구문: DB::DB::connection -> 올바른 구문: DB::connection
    $shopTypes = DB::connection('dynamic')->table('shopping_mall_integrations')
                    ->select('platform')
                    ->distinct()
                    ->pluck('platform');

    \Log::info('가져온 쇼핑몰 유형: ', $shopTypes->toArray());

    return response()->json(['shop_types' => $shopTypes]);
}


public function getTotalProductCount($mallArray, $params)
{
    $url = "https://{$mallArray['mall_id']}.cafe24api.com/api/v2/admin/products/count";

    $accessToken = $mallArray['access_token'];
    $expiresAt = Carbon::parse($mallArray['expires_at']);
    $refreshToken = $mallArray['refresh_token'];
    $refreshTokenExpiresAt = Carbon::parse($mallArray['refresh_token_expires_at']);
    $userId = $mallArray['user_id'];

    // ✅ Access Token 만료 시 Cafe24ApiService를 통해 자동 갱신
    if (Carbon::now()->greaterThanOrEqualTo($expiresAt)) {
        \Log::info('상품 개수 조회 시 Access Token 만료됨. Refresh Token을 사용하여 갱신 시도.');

        if (Carbon::now()->greaterThanOrEqualTo($refreshTokenExpiresAt)) {
            \Log::error('Refresh Token도 만료되었습니다.');
            return 0;
        }

        $result = $this->cafe24ApiService->refreshAccessToken($mallArray['mall_id'], $refreshToken, $userId);
        
        if (isset($result['success']) && $result['success']) {
            $accessToken = $result['access_token'];

            // ✅ DB에서 최신화된 mall 데이터를 다시 가져옴
            $mallArray = DB::connection('dynamic')
                ->table('shopping_mall_integrations')
                ->where('mall_id', $mallArray['mall_id'])
                ->first();

            \Log::info('갱신 후 최신 Mall 데이터:', (array)$mallArray);
            
            $accessToken = $mallArray->access_token; // 최신 토큰 사용
        } else {
            \Log::error('Access Token 갱신 실패.');
            return 0;
        }
    }

    $headers = [
        "Authorization: Bearer {$accessToken}",
        "Content-Type: application/json",
        "X-Cafe24-Api-Version: 2024-12-01"
    ];

    $queryParams = http_build_query($params);
    $fullUrl = $url . '?' . $queryParams;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['count'])) {
        return $data['count'];
    } else {
        \Log::error("상품 개수 조회 실패: " . json_encode($response));
        return 0;
    }
}



     

}
