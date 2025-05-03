<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecyclingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'waste_item_id',
        'location',
        'image',
        'points_earned',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wasteItem()
    {
        return $this->belongsTo(WasteItem::class);
    }
}
