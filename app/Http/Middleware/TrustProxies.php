<?php
namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    protected $proxies = '*'; // Cloudflare 프록시 허용
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
