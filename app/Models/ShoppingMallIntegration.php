<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShoppingMallIntegration extends Model
{
    use HasFactory;

    protected $table = 'shopping_mall_integrations'; // ✅ 테이블 이름

    protected $fillable = [
        'user_id',
        'mall_id',
        'platform',
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'vendor_id',
        'access_key',
        'secret_key',
        'expires_at',
    ];

    /**
     * ✅ 사용자별 동적 데이터베이스 연결을 설정하는 메서드
     */
    public function setConnectionByUser($userId)
    {
        $dbName = "sellflow_global_{$userId}"; // ✅ 사용자별 DB 네이밍 규칙 적용
        $this->setConnection($dbName); 
        return $this;
    }
}
