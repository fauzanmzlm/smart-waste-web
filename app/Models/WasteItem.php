<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'waste_type_id',
        'name',
        'description',
        'image',
        'recyclable',
        'disposal_instructions',
        'restrictions',
        'alternatives',
        'points',
        'ocean_impact_factors',
    ];

    protected $casts = [
        'recyclable' => 'boolean',
        'disposal_instructions' => 'array',
        'ocean_impact_factors' => 'array',
    ];

    public function wasteType()
    {
        return $this->belongsTo(WasteType::class);
    }

    public function recyclingHistories()
    {
        return $this->hasMany(RecyclingHistory::class);
    }
}
