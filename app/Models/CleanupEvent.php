<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleanupEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'time',
        'location',
        'organizer',
        'description',
        'image',
        'latitude',
        'longitude',
        'website',
        'contact_number',
    ];

    protected $casts = [
        'date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
