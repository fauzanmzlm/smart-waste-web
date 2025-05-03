<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'description',
        'tips',
        'class',
    ];

    public function wasteItems()
    {
        return $this->hasMany(WasteItem::class);
    }

    public function totalWasteItems(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->wasteItems()->count();
        });
    }

    public function recyclingCenters()
    {
        return $this->belongsToMany(RecyclingCenter::class, 'recycling_center_waste_type');
    }
}
