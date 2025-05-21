<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiGatewayController extends Controller
{
    public function requestCafe24API(Request $request)
    {
        // ✅ Cafe24 연동 정보 받기
        $mall_id = $request->input('mall_id');  // 쇼핑몰 도메인
        $endpoint = $request->input('endpoint'); // Cafe24 API 엔드포인트
        $method = $request->input('method', 'GET'); // 요청 방식 (기본 GET)
        $access_token = $request->input('access_token');
        $payload = $request->input('payload', []);

         // ✅ 유효성 검사 (필수 파라미터 확인)
         if (!$mall_id || !$endpoint || !$access_token) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // ✅ Cafe24 API URL 생성
        $url = "https://{$mall_id}.cafe24api.com/api/v2/{$endpoint}";


        // ✅ API 요청 실행
        $response = Http::withHeaders([
            'Authorization' => "Bearer $access_token",
            'Content-Type' => 'application/json',
        ])->get("https://$mall_id.cafe24api.com/api/v2/admin/orders");
        
        $orders = $response->json();

        //📌 서버 분리 후에는 외부 API Gateway 서버 호출
        // $response = Http::post('https://api-gateway.sellflow.com/api-gateway/cafe24', [
        //     'mall_id' => $mall_id,
        //     'endpoint' => 'admin/orders',
        //     'method' => 'GET',
        //     'access_token' => $access_token,
        // ]);
        

        return response()->json($response->json(), $response->status());
    }
}
