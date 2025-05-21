<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cafe24Webhook extends Model
{
    use HasFactory;

    protected $table = 'cafe24_webhooks'; // ✅ 테이블 이름 명시
    protected $fillable = ['mall_id', 'event_type', 'payload', 'received_at'];

    protected $casts = [
        'payload' => 'array', // ✅ JSON 데이터를 자동 변환
        'received_at' => 'datetime',
    ];
}
