<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * 사용자가 연동한 쇼핑몰 유형만 가져오는 API
     */
    public function getShopTypes()
    {
        $userId = Auth::id(); // 현재 로그인한 사용자 ID
        if (!$userId) {
            return response()->json(['error' => '사용자가 인증되지 않았습니다.'], 401);
        }

        // 사용자가 연동한 쇼핑몰 유형만 가져오기
        $shopTypes = DB::table('shopping_mall_integrations')
            ->where('user_id', $userId)
            ->pluck('shop_type')
            ->unique()
            ->toArray();

        return response()->json(['shop_types' => $shopTypes]);
    }

    /**
     * 선택된 쇼핑몰 유형에 해당하는 계정 목록을 가져오는 API
     */
    public function getShopAccounts(Request $request)
    {
        $userId = Auth::id();
        $shopType = $request->input('shop_type');

        if (!$userId) {
            return response()->json(['error' => '사용자가 인증되지 않았습니다.'], 401);
        }

        if (!$shopType) {
            return response()->json(['error' => '쇼핑몰 유형이 누락되었습니다.'], 400);
        }

        $accounts = DB::table('shopping_mall_integrations')
            ->where('user_id', $userId)
            ->where('shop_type', $shopType)
            ->pluck('mall_id')
            ->toArray();

        return response()->json(['accounts' => $accounts]);
    }

    /**
     * 상품 데이터를 가져오는 API
     */
    public function getProducts()
    {
        $userId = Auth::id();
        $dbName = "sellflow_global_{$userId}";

        if (!$userId) {
            return response()->json(['error' => '사용자가 인증되지 않았습니다.'], 401);
        }

        config(['database.connections.dynamic.database' => $dbName]);
        DB::purge('dynamic');

        $products = DB::connection('dynamic')
    ->table('shopping_mall_products_temp')
    ->select('product_id', 'product_name', 'product_code', 'price', 'status', 'stock', 'main_image_url', 'option_name') // ✅ 추가
    ->where('status', '!=', '제외')
    ->get();


        return response()->json(['products' => $products]);
    }
}
