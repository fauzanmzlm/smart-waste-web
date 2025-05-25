<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassificationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'waste_type_id',
        'image',
        'confidence_score',
    ];

    protected $casts = [
        'confidence_score' => 'float',
    ];

    /**
     * Get the user that owns this classification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the waste type for this classification.
     */
    public function wasteType()
    {
        return $this->belongsTo(WasteType::class);
    }
    
}
