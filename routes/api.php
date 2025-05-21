<?php

use App\Http\Controllers\Cafe24WebhookController;
use App\Http\Controllers\ApiGatewayController;
use Illuminate\Support\Facades\Route;


// ✅ API Gateway (Cafe24 API 요청용)
Route::post('/api-gateway/cafe24', [ApiGatewayController::class, 'requestCafe24API'])->name('api.gateway.cafe24');

// ✅ Webhook (Cafe24 Webhook 수신용)
Route::post('/webhook/cafe24', [Cafe24WebhookController::class, 'handleWebhook']);
