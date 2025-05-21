<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppSetupController extends Controller
{
    /**
     * 설치 완료 후 success 페이지를 보여주는 메서드
     */
    public function successPage(Request $request)
{
    $mallId = $request->query('mall_id');
    $authCode = $request->query('auth_code'); // OAuth 인증 코드

    // if (!$mallId || !$authCode) {
    //     return redirect('/login')->withErrors(['message' => '잘못된 접근입니다.']);
    // }

    // 쇼핑몰 연동 정보 확인
    $isLinked = DB::table('shopping_mall_integrations')
                  ->where('mall_id', $mallId)
                  ->exists();

    return view('app.success', [
        'mall_id' => $mallId,
        'auth_code' => $authCode,
        'isLinked' => $isLinked
    ]);
}

}
