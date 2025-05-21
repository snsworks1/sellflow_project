<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * CSRF 예외 처리를 할 URL 목록
     *
     * @var array
     */
    protected $except = [
        'api/webhook/cafe24', // ✅ Cafe24 Webhook 예외 처리
        'api/webhook/*',       // ✅ 다른 Webhook도 처리 가능하도록 설정
    ];
}
