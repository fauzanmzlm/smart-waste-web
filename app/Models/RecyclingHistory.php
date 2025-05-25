<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecyclingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'center_id',
        'waste_item_id',
        'waste_name',
        'quantity',
        'unit',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function center()
    {
        return $this->belongsTo(RecyclingCenter::class);
    }

    public function wasteItem()
    {
        return $this->belongsTo(WasteItem::class);
    }

    // Add this method for the polymorphic relationship with PointsTransaction
    public function pointsTransaction()
    {
        return $this->morphOne(PointsTransaction::class, 'transactionable');
    }
}