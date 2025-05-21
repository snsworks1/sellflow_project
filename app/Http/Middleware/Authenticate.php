<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * 사용자가 인증되지 않은 경우 리디렉션할 경로 지정
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * 특정 경로에서는 인증을 예외 처리
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if ($request->is('oauth/callback') || $request->is('app/success')) {
            return $next($request); // ⬅️ OAuth 콜백과 success 페이지는 인증 예외 처리
        }
    
        if (!$request->user()) {
            return redirect()->route('login');
        }
    
        return $next($request);
    }
}
