<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\BusinessInfo;
use App\Services\Cafe24ApiService;


class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {

        if (config('app.env') !== 'local') {
            URL::forceScheme('https'); // 운영 환경에서 HTTPS 강제 적용
        }
        // ✅ 쇼핑몰 목록을 전역적으로 공유
        View::share('shoppingMalls', [
            ['name' => 'Cafe24', 'link' => '/settings/integration?platform=cafe24'],
            ['name' => '스마트스토어', 'link' => '/settings/integration?platform=smartstore'],
            ['name' => '쿠팡', 'link' => '/settings/integration?platform=coupang'],
        ]);

        // ✅ 현재 로그인된 사용자의 비즈니스 정보 공유
        View::composer('*', function ($view) {
            $user = Auth::user();
            $businessInfo = $user ? BusinessInfo::where('user_id', $user->id)->first() : null;
            $view->with('businessInfo', $businessInfo);
        });
    }
    public function register()
{
    $this->app->singleton(Cafe24ApiService::class, function ($app) {
        return new Cafe24ApiService();
    });
}
}
