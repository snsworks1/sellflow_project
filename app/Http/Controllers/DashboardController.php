<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessInfo; // BusinessInfo 모델 추가

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // 공지사항 예시 데이터
        $sellflowNotices = [
            ['title' => '[업데이트] 새로운 대시보드 기능', 'date' => '2025-01-24', 'link' => '#'],
            ['title' => '[공지] 서비스 점검 일정 안내', 'date' => '2025-01-22', 'link' => '#'],
        ];

        $shoppingMallNotices = [
            ['title' => '[공지] 쇼핑몰 업데이트 안내', 'date' => '2025-01-24', 'link' => '#'],
            ['title' => '[안내] 신규 기능 추가', 'date' => '2025-01-20', 'link' => '#'],
        ];

        // 쇼핑몰 리스트 예시
        $shoppingMalls = [
            ['name' => 'cafe24 | 계정명', 'link' => '/mall/1'],
            ['name' => '스마트스토어 | 계정명', 'link' => '/mall/2'],
        ];

        // 비즈니스 정보 가져오기
        $businessInfo = null;
        if ($user->account_type === 'business') {
            $businessInfo = BusinessInfo::where('user_id', $user->id)->first();
        }

        // 뷰로 데이터 전달
        return view('dashboard', compact('user', 'sellflowNotices', 'shoppingMallNotices', 'shoppingMalls', 'businessInfo'));
    }
}
