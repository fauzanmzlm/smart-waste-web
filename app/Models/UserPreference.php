<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'data_usage',
        'dark_mode',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'data_usage' => 'boolean',
        'dark_mode' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
