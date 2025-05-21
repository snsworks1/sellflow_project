<?php

namespace App\Http\Controllers;
use App\Models\OauthIntegration; // ✅ 네임스페이스 추가
use Illuminate\Http\Request;
use App\Models\ShoppingMallIntegration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Services\Cafe24ApiService;






use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class IntegrationController extends Controller
{
    // ✅ 쇼핑몰 연동 목록 페이지
    public function index()
{
    $userId = Auth::id();
    $dbName = "sellflow_global_{$userId}";

    // ✅ 동적으로 사용자 데이터베이스 설정
    config(['database.connections.dynamic.database' => $dbName]);
    DB::purge('dynamic');

    // ✅ 올바른 데이터 가져오기 (객체 형태로 반환)
    $shoppingMalls = ShoppingMallIntegration::on('dynamic')->get();


    return view('settings.integration', compact('shoppingMalls'));
}


    // ✅ 쇼핑몰 연동 추가 페이지
    public function create()
    {
        return view('settings.integration_add');
    }

    // ✅ 쇼핑몰 연동 저장
    public function store(Request $request)
    {
        $request->validate([
            'platform' => 'required|string',
            'mall_id' => 'required|string|unique:shopping_mall_integrations,mall_id',
        ]);

        $userId = Auth::id();
        $dbName = "sellflow_global_{$userId}";

        // 동적으로 사용자 데이터베이스 설정
        config(['database.connections.dynamic.database' => $dbName]);
        DB::purge('dynamic');

        // 저장할 데이터 필드
        $data = [
            'user_id' => $userId,
            'mall_id' => $request->mall_id,
            'platform' => $request->platform,
        ];

        // ✅ 플랫폼별 추가 필드 저장
        if ($request->platform === 'cafe24') {
            $data['client_id'] = $request->client_id;
            $data['client_secret'] = $request->client_secret;
        } elseif ($request->platform === 'smartstore') {
            $data['client_id'] = $request->client_id;
            $data['client_secret'] = $request->client_secret;
            $data['access_token'] = $request->access_token;
            $data['refresh_token'] = $request->refresh_token;
        } elseif ($request->platform === 'coupang') {
            $data['vendor_id'] = $request->vendor_id;
            $data['access_key'] = $request->access_key;
            $data['secret_key'] = $request->secret_key;
        }

        // ✅ 쇼핑몰 연동 정보 저장
        ShoppingMallIntegration::on('dynamic')->create($data);

        return redirect()->route('integration.index')->with('success', '쇼핑몰 연동이 완료되었습니다.');
    }

    // ✅ 연동된 쇼핑몰 삭제
    public function destroy($id)
    {
        $userId = Auth::id();
        $dbName = "sellflow_global_{$userId}";

        // 동적으로 사용자 데이터베이스 설정
        config(['database.connections.dynamic.database' => $dbName]);
        DB::purge('dynamic');

        // ✅ 삭제 처리
        ShoppingMallIntegration::on('dynamic')->where('id', $id)->delete();

        return redirect()->route('integration.index')->with('success', '연동이 삭제되었습니다.');
    }

     // ✅ 쇼핑몰 연동 수정 페이지
     public function edit($id)
     {
         $userId = Auth::id();
         $dbName = "sellflow_global_{$userId}"; 
     
         config(['database.connections.dynamic.database' => $dbName]);
         DB::purge('dynamic');
         DB::reconnect('dynamic');
     
         // ✅ 통합 정보 불러오기
         $integration = ShoppingMallIntegration::on('dynamic')->findOrFail($id);
     
         // ✅ 리프레시 토큰 만료까지 남은 시간 계산
         $refreshExpiresAt = Carbon::parse($integration->refresh_token_expires_at);
         $now = Carbon::now();
     
         $remainingSeconds = $now->diffInSeconds($refreshExpiresAt, false);
         $expirationDate = $refreshExpiresAt->format('Y-m-d H:i:s');
     
         // ✅ 만료 여부 추가 (뷰에서 @if 사용 가능)
         $refreshTokenExpired = $remainingSeconds <= 0;
     
         return view('settings.integration_edit', compact(
             'integration',
             'remainingSeconds',
             'expirationDate',
             'refreshTokenExpired' // 👈 여기를 추가해줍니다
         ));
     }
     
     

 
     // ✅ 쇼핑몰 연동 업데이트
     public function update(Request $request, $id)
     {
         $request->validate([
             'platform' => 'required|string',
             'mall_id' => 'required|string',
         ]);
 
         $userId = Auth::id();
         $dbName = "sellflow_global_{$userId}";
 
         config(['database.connections.dynamic.database' => $dbName]);
         DB::purge('dynamic');
 
         $integration = ShoppingMallIntegration::on('dynamic')->findOrFail($id);
 
         $integration->update([
             'mall_id' => $request->mall_id,
             'platform' => $request->platform,
             'client_id' => $request->client_id,
             'client_secret' => $request->client_secret,
             'access_token' => $request->access_token,
             'refresh_token' => $request->refresh_token,
             'vendor_id' => $request->vendor_id,
             'access_key' => $request->access_key,
             'secret_key' => $request->secret_key,
         ]);
 
         return redirect()->route('integration.index')->with('success', '쇼핑몰 연동 정보가 수정되었습니다.');
     }

     public function redirectToOAuth(Request $request)
     {
         $mallId = $request->query('mall_id');
 
         // ✅ mall_id가 입력되지 않았을 경우
    if (!$mallId) {
        return redirect()->back()->with('error', 'Mall ID를 입력하세요.');
    }

    // ✅ mall_id 형식이 올바른지 체크 (영문 소문자, 숫자, 하이픈만 가능)
    if (!preg_match('/^[a-z0-9-]+$/', $mallId)) {
        return redirect()->back()->with('error', '유효하지 않은 형식의 Mall ID입니다.');
    }

  // ✅ 존재 여부 확인 (admin/shop API 활용)
  try {
    $response = Http::get("https://{$mallId}.cafe24api.com/api/v2/oauth/authorize");

    // ✅ 400 또는 404가 반환되면 존재하지 않는 mall_id로 판단
    if ($response->status() >= 400) {
        return redirect()->back()->with('error', '존재하지 않는 Mall ID입니다.');
    }
} catch (\Exception $e) {
    return redirect()->back()->with('error', '네트워크 오류 또는 존재하지 않는 Mall ID입니다.');
}

 
         $clientId = env('CAFE24_CLIENT_ID');
         $redirectUri = route('app.oauth.callback');
         $scope = "mall.read_application,mall.write_application,mall.read_product";
 
         // ✅ 새로운 state 값 생성
         $state = base64_encode(json_encode([
             'mall_id' => $mallId,
             'timestamp' => time(),
         ]));
         Session::put('oauth_state', $state);
 
         // ✅ PKCE: code_verifier 생성
         $codeVerifier = bin2hex(random_bytes(64));
         Session::put('oauth_code_verifier', $codeVerifier);
 
         // ✅ PKCE: code_challenge 생성
         $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
 
         // ✅ OAuth 인증 URL 생성
         $oauthUrl = "https://{$mallId}.cafe24api.com/api/v2/oauth/authorize"
             . "?response_type=code"
             . "&client_id={$clientId}"
             . "&state={$state}"
             . "&redirect_uri=" . urlencode($redirectUri)
             . "&scope=" . urlencode($scope)
             . "&code_challenge={$codeChallenge}"
             . "&code_challenge_method=S256";

           
 
         return redirect()->away($oauthUrl);
     }
 
     /**
      * OAuth Callback (Access Token 요청 및 저장)
      */
      public function handleOAuthCallback(Request $request)
      {

        \Log::info('📥 OAuth Callback 호출됨', [
            'query' => $request->query(),
        ]);
          $code = $request->input('code');
          $decodedState = json_decode(base64_decode($request->input('state')), true);
          $mallId = $decodedState['mall_id'] ?? null;
          $userId = $decodedState['user_id'] ?? Auth::id(); // 기존 설치용에는 없음
          $integrationId = $decodedState['integration_id'] ?? null;
      
          if (!$mallId || !$code) {
              return redirect()->route('integration.index')->with('error', '잘못된 요청입니다. 다시 시도해주세요.');
          }
      
          // ✅ PKCE: code_verifier 가져오기
          $codeVerifier = Session::get('oauth_code_verifier');
      
          // ✅ 토큰 요청
          $clientId = env('CAFE24_CLIENT_ID');
          $clientSecret = env('CAFE24_CLIENT_SECRET');
          $redirectUri = route('app.oauth.callback'); // /oauth/callback 고정
      
          try {
              $client = new \GuzzleHttp\Client();
              $response = $client->post("https://{$mallId}.cafe24api.com/api/v2/oauth/token", [
                  'headers' => [
                      'Authorization' => 'Basic ' . base64_encode("$clientId:$clientSecret"),
                      'Content-Type'  => 'application/x-www-form-urlencoded',
                  ],
                  'form_params' => [
                      'grant_type'    => 'authorization_code',
                      'code'          => $code,
                      'redirect_uri'  => $redirectUri,
                  ],
              ]);
          } catch (\Exception $e) {
              return redirect()->route('integration.index')->with('error', '토큰 요청 실패: ' . $e->getMessage());
          }
      
          $data = json_decode($response->getBody(), true);

          $accessTokenExpireAt = Carbon::now()->addSeconds($data['expires_in'] ?? 3600);
$refreshTokenExpireAt = isset($data['refresh_token_expires_at'])
    ? Carbon::parse($data['refresh_token_expires_at'])
    : Carbon::now()->addDays(14);

          if (!isset($data['access_token'])) {
              return redirect()->route('integration.index')->with('error', '엑세스 토큰이 없습니다.');
          }
      
          // ✅ 토큰 파싱
          $accessToken = $data['access_token'];
          $refreshToken = $data['refresh_token'];
          
          $accessTokenExpiresAt = isset($data['expires_in'])
          ? Carbon::now()->addSeconds($data['expires_in'])
          : Carbon::now()->addMinutes(30);
      
      $refreshTokenExpiresAt = isset($data['refresh_token_expires_at'])
          ? Carbon::parse($data['refresh_token_expires_at']) // ✅ 여기
          : Carbon::now()->addDays(14);
      

  
          // ✅ 통합: 재연동인 경우
          if ($userId && $integrationId) {
              // 메인 DB 업데이트
              OauthIntegration::where('mall_id', $mallId)->update([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => $accessTokenExpireAt,
                'refresh_token_expires_at' => $refreshTokenExpireAt,
            ]);
      
              // 유저 DB 업데이트
              $dbName = "sellflow_global_{$userId}";
              config(['database.connections.dynamic.database' => $dbName]);
              DB::purge('dynamic');
      
              ShoppingMallIntegration::on('dynamic')->where('id', $integrationId)->update([
                  'access_token' => $accessToken,
                  'refresh_token' => $refreshToken,
'expires_at' => $accessTokenExpireAt,
                  'refresh_token_expires_at' => $refreshTokenExpiresAt,
              ]);
      
              return redirect()->route('integration.index')->with('success', '재연동이 완료되었습니다.');
          }
      
          // ✅ 설치용 흐름
          $platform = 'cafe24';
          $userId = Auth::id();
      
          // 이미 연동되어 있는 mall_id인지 확인
          $exists = DB::table("sellflow_global_{$userId}.shopping_mall_integrations")
              ->where('mall_id', $mallId)->exists();
      
          if ($exists) {
              return redirect()->route('integration.index')->with('error', "{$mallId}는 이미 연동된 쇼핑몰입니다.");
          }
      
          // ✅ 새로 연동 저장
          DB::transaction(function () use ($userId, $mallId, $platform, $clientId, $clientSecret, $accessToken, $refreshToken, $accessTokenExpiresAt, $refreshTokenExpiresAt) {
              // 메인 DB
              OauthIntegration::updateOrCreate(
                  ['mall_id' => $mallId],
                  [
                      'user_id' => $userId,
                      'access_token' => $accessToken,
                      'refresh_token' => $refreshToken,
'expires_at' => $accessTokenExpireAt,
                      'refresh_token_expires_at' => $refreshTokenExpiresAt,
                  ]
              );
      
              // 유저 DB
              DB::table("sellflow_global_{$userId}.shopping_mall_integrations")->updateOrInsert(
                  ['user_id' => $userId, 'mall_id' => $mallId],
                  [
                      'platform' => $platform,
                      'client_id' => $clientId,
                      'client_secret' => $clientSecret,
                      'access_token' => $accessToken,
                      'refresh_token' => $refreshToken,
'expires_at' => $accessTokenExpireAt,
                      'refresh_token_expires_at' => $refreshTokenExpiresAt,
                      'created_at' => now(),
                      'updated_at' => now(),
                  ]
              );
          });
      
          return redirect()->route('integration.index')->with('success', "{$mallId} 연동이 완료되었습니다.");
      }
      


      public function reintegrate($id)
{
    $mall = ShoppingMallIntegration::find($id);

    if (!$mall) {
        return redirect()->back()->with('error', '연동된 쇼핑몰을 찾을 수 없습니다.');
    }

    // ✅ OAuth 재연동을 위한 기존 데이터 유지
    $mallId = $mall->mall_id;

    return redirect()->route('integration.redirect', ['mall_id' => $mallId]);
}

     

protected $cafe24ApiService;

public function __construct(Cafe24ApiService $cafe24ApiService)
{
    $this->cafe24ApiService = $cafe24ApiService;
}


     
public function testIntegration(Request $request)
{
    try {
        $mallId = $request->query('mall_id');

        if (!$mallId) {
            return response()->json(['success' => false, 'message' => '쇼핑몰 ID가 필요합니다.'], 400);
        }

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => '사용자가 인증되지 않았습니다.'], 401);
        }

        $dbName = "sellflow_global_{$userId}";
        Config::set('database.connections.dynamic.database', $dbName);
        DB::purge('dynamic');
        DB::reconnect('dynamic');

        $mall = ShoppingMallIntegration::on('dynamic')->where('mall_id', $mallId)->first();

        if (!$mall) {
            return response()->json(['success' => false, 'message' => '연동된 쇼핑몰을 찾을 수 없습니다.'], 404);
        }

        // ✅ 엑세스 토큰 갱신 로직 추가
        if (Carbon::parse($mall->expires_at)->lt(Carbon::now()->addMinutes(10))) {
            \Log::info('리프레시 토큰: ' . $mall->refresh_token);
            \Log::info('엑세스 토큰 만료 시각: ' . $mall->expires_at);
\Log::info('현재 시각: ' . now());

            try {
                $refreshResult = $this->cafe24ApiService->refreshAccessToken($mallId, $mall->refresh_token, $userId);

                if (!$refreshResult['success']) {
                    \Log::error('토큰 갱신 실패 응답: ' . $refreshResult['message']);
                    return response()->json([
                        'success' => false,
                        'message' => '엑세스 토큰 갱신에 실패했습니다: ' . $refreshResult['message']
                    ], 500);
                }

                $mall->access_token = $refreshResult['access_token'];
                $mall->expires_at = $refreshResult['expires_at'];
                $mall->save();

            } catch (\Exception $e) {
                \Log::error('토큰 갱신 중 예외 발생: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => '엑세스 토큰 갱신에 실패했습니다: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '연동된 쇼핑몰을 찾았습니다.',
            'mall' => $mall
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => '서버 오류가 발생했습니다.',
            'error' => $e->getMessage()
        ], 500);
    }
}





    public function successPage()
{
    return view('success');
}


public function reauth(Request $request, $id)
{
    
    $userId = Auth::id();
    $dbName = "sellflow_global_{$userId}";
    config(['database.connections.dynamic.database' => $dbName]);
    DB::purge('dynamic');

    $integration = ShoppingMallIntegration::on('dynamic')->findOrFail($id);

    $clientId = config('services.cafe24.client_id');
    $redirectUri = route('app.oauth.callback'); // ✅ 정확한 route 기반 URL

    $state = base64_encode(json_encode([
        'user_id' => $userId,
        'mall_id' => $integration->mall_id,
        'integration_id' => $integration->id,
    ]));

    $url = "https://{$integration->mall_id}.cafe24api.com/api/v2/oauth/authorize"
    . "?response_type=code"
    . "&client_id={$clientId}"
    . "&redirect_uri={$redirectUri}"
    . "&state={$state}"
    . "&scope=mall.read_application,mall.write_application,mall.read_product"; // ✅ scope 추가


    return redirect()->away($url);
}





public function collectProducts(Request $request)
{
    $mallId = $request->query('mall_id');
    $userId = Auth::id();

    if (!$mallId || !$userId) {
        return response()->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
    }

    $dbName = "sellflow_global_{$userId}";
    Config::set('database.connections.dynamic.database', $dbName);
    DB::purge('dynamic');
    DB::reconnect('dynamic');

    $mall = ShoppingMallIntegration::on('dynamic')->where('mall_id', $mallId)->first();

    if (!$mall) {
        return response()->json(['success' => false, 'message' => '연동된 쇼핑몰을 찾을 수 없습니다.'], 404);
    }

    // ✅ API 요청 시 엑세스 토큰 만료 확인 및 갱신
    if (Carbon::parse($mall->expires_at)->lt(Carbon::now()->addMinutes(10))) {
        $refreshResult = $this->cafe24ApiService->refreshAccessToken($mallId, $mall->refresh_token, $userId);
        if (!$refreshResult['success']) {
            return response()->json(['success' => false, 'message' => $refreshResult['message']], 500);
        }
        $mall->access_token = $refreshResult['access_token'];
        $mall->expires_at = $refreshResult['expires_at'];
        $mall->save();
    }

    // ✅ 상품 데이터 수집
    $result = $this->cafe24ApiService->fetchProducts($mall->mall_id, $mall->access_token, $userId);

    if (!$result['success']) {
        return response()->json(['success' => false, 'message' => $result['message']], 500);
    }

    // ✅ 수집된 상품을 사용자별 데이터베이스에 저장
    foreach ($result['products'] as $product) {
        DB::connection('dynamic')->table('shopping_mall_products')->updateOrInsert([
            'product_id' => $product['product_no'],
        ], [
            'shop_type' => 'Cafe24',
            'shop_account' => $mall->mall_id,
            'product_code' => $product['sku'] ?? $product['product_code'],
            'product_name' => $product['product_name'],
            'category' => $product['category']['name'] ?? null,
            'price' => $product['price'],
            'original_price' => $product['original_price'] ?? $product['supply_price'],
            'stock' => $product['stock']['quantity'] ?? $product['stock'],
            'main_image_url' => $product['image']['url'] ?? $product['representative_image'] ?? null,
            'model_name' => $product['model_name'] ?? null,
            'supplier_name' => $product['supplier_name'] ?? null,
            'status' => $product['selling'] ? '판매중' : '중지',
            'supply_price' => $product['supply_price'],
            'adult_certification' => $product['adult_certification'] ?? false,
            'option_name' => $product['options'][0]['name'] ?? null,
            'manufacturer' => $product['manufacturer'] ?? null,
            'brand' => $product['brand'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return response()->json(['success' => true, 'message' => '상품 수집이 완료되었습니다.']);
}



}
