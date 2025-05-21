<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // 항상 메인 데이터베이스를 사용하도록 설정
    protected $connection = 'main';

    protected $fillable = [
        'name',
        'email',
        'password',
        'account_type',
        'alarm_agreement',
    ];
}