<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSetting extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_at',
        'spreadsheet_map',
    ];

    protected $casts = [
        'expires_at'     => 'datetime',
        'spreadsheet_map' => 'array',
    ];
}
