<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'owner_name',
        'registration_number',
    ];
}
