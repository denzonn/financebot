<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSheet extends Model
{
    protected $table = 'google_sheets';

    protected $fillable = [
        'user_id',
        'spreadsheet_id',
        'spreadsheet_name',
        'sheet_name',
        'spreadsheet_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
