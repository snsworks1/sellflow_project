<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ProductController;

// 기존 web 미들웨어 그룹 안에 API 라우트를 포함
Route::middleware(['auth'])->group(function () {
    Route::get('/products/get-shop-types', [ProductController::class, 'getShopTypes']);
    Route::get('/products/get-accounts', [ProductController::class, 'getShopAccounts']);
    Route::get('/api/products/get-products', [ProductController::class, 'getProducts']);
    Route::post('/products/import', [ProductImportController::class, 'importProducts'])->name('products.import');

});

Route::get('/integration/reauth/{id}', [IntegrationController::class, 'reauth'])->name('integration.reauth'); //재연동 
Route::get('/oauth/callback', [IntegrationController::class, 'callback'])->name('app.oauth.callback');
Route::get('/settings/integration/reauth/{id}', [IntegrationController::class, 'reauth'])->name('integration.reauth');


Route::get('/products/import', [ProductImportController::class, 'showImportPage'])->name('products.import');
Route::post('/products/import', [ProductImportController::class, 'importProducts'])->name('products.import.post');
Route::get('/products/accounts', [ProductImportController::class, 'getAccounts']);
Route::get('/products/accounts', [ProductImportController::class, 'getAccounts'])->name('products.accounts');
Route::get('/products/get-accounts', [ProductImportController::class, 'getAccounts']);
Route::get('/products/get-shop-types', [ProductImportController::class, 'getShopTypes']);

Route::get('/products/collect', [IntegrationController::class, 'collectProducts'])->name('products.collect');


Route::get('/test-integration', [IntegrationController::class, 'testIntegration']);

Route::get('/settings/integration/oauth-redirect', [IntegrationController::class, 'redirectToOAuth'])->name('integration.redirect');
Route::get('/app/success', [IntegrationController::class, 'successPage'])->name('app.success');

Route::post('/integration/validate', [IntegrationController::class, 'validateMallCredentials'])->name('integration.validate');

// 쇼핑몰 연동 페이지
Route::get('/app/integration', [IntegrationController::class, 'integrationPage'])->name('app.integration');

// Cafe24 OAuth 리디렉션
Route::get('/oauth/redirect', [IntegrationController::class, 'redirectToCafe24OAuth'])->name('app.oauth.redirect');

// OAuth Callback (Access Token 발급)
Route::get('/oauth/callback', [IntegrationController::class, 'handleOAuthCallback'])->name('app.oauth.callback');

// 성공 페이지

// 사용자 연동된 쇼핑몰 목록
Route::get('/app/malls', [IntegrationController::class, 'getUserShoppingMalls'])->name('app.malls');






Route::get('/', function () {
    return view('auth.login');
});

Route::get('/test-css', function () {
    return response()->json([
        'css_path' => public_path('build/assets/app.css'),
        'file_exists' => file_exists(public_path('build/assets/app.css'))
    ]);
});


Route::get('/login', [YourAuthController::class, 'showLoginForm'])->name('login');


Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
// Route::get('/register', [RegisterController::class, 'show'])->name('register');
// Route::post('/register', [RegisterController::class, 'store']);

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);
Route::get('/password/request', [AuthController::class, 'showPasswordRequestForm'])->name('password.request');

// 이메일 인증 요청
Route::post('/verify-email', [AuthController::class, 'sendVerificationEmail']);

// 이메일 인증 확인
Route::get('/verify-email/confirm', [AuthController::class, 'confirmVerification']);
Route::get('/email-verification-status', [AuthController::class, 'getVerificationStatus']);
Route::post('/invalidate-email-verification', [AuthController::class, 'invalidateVerification']);



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/dashboard', function () {
    return view('dashboard'); // 기본 홈
});

Route::get('/orders', function () {
    return view('orders'); // 주문 관리 페이지 (추가할 때)
});

Route::get('/settings', function () {
    return view('settings'); // 설정 페이지 (추가할 때)
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');



Route::prefix('settings')->group(function () {
    Route::get('/integration', [IntegrationController::class, 'index'])->name('integration.index'); // 쇼핑몰 연동 목록
    Route::get('/integration/add', [IntegrationController::class, 'create'])->name('integration.create'); // 연동 추가
    Route::post('/integration/store', [IntegrationController::class, 'store'])->name('integration.store'); // 연동 저장
    Route::get('/integration/edit/{id}', [IntegrationController::class, 'edit'])->name('integration.edit'); // 연동 수정 페이지
    Route::put('/integration/update/{id}', [IntegrationController::class, 'update'])->name('integration.update'); // 연동 업데이트
    Route::delete('/integration/{id}', [IntegrationController::class, 'destroy'])->name('integration.destroy'); // 연동 삭제
});