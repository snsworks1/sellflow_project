<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OauthIntegration extends Model
{
    use HasFactory;

    protected $connection = 'sellflow'; // ✅ 메인 DB 연결
    protected $table = 'oauth_integrations';

    protected $fillable = [
        'mall_id',       // 쇼핑몰 ID
        'platform',      // 플랫폼 종류 (Cafe24, Smartstore 등)
        'access_token',
        'refresh_token',
        'expires_at',
    ];
}
