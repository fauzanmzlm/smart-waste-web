<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecyclingCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'website',
        'description',
        'latitude',
        'longitude',
        'hours',
        'image',
        'user_id',
        'status',
        'rejection_reason',
        'is_active',
        'points_multiplier',
    ];

    protected $casts = [
        'hours' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function wasteTypes()
    {
        return $this->belongsToMany(WasteType::class, 'recycling_center_waste_type');
    }

    // Calculate distance from given coordinates
    public function getDistanceAttribute($lat = null, $lng = null)
    {
        if (!$lat || !$lng) {
            return null;
        }

        // Earth radius in miles
        $earthRadius = 3959;

        $lat1 = deg2rad($this->latitude);
        $lng1 = deg2rad($this->longitude);
        $lat2 = deg2rad($lat);
        $lng2 = deg2rad($lng);

        $dLat = $lat2 - $lat1;
        $dLng = $lng2 - $lng1;

        $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 1);
    }


    /**
     * Get the rewards offered by this center.
     */
    public function rewards()
    {
        return $this->hasMany(Reward::class, 'center_id');
    }

    /**
     * Get the material point configurations for this center.
     */
    public function materialPointConfigs()
    {
        return $this->hasMany(MaterialPointConfig::class, 'center_id');
    }

    /**
     * Get the points transactions for this center.
     */
    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class, 'center_id');
    }

    /**
     * Get the bonus configuration for this center.
     */
    public function bonusConfig()
    {
        return $this->hasOne(BonusConfig::class, 'center_id');
    }

    /**
     * Get the user who owns this center.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include active centers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include approved centers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending centers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include rejected centers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to find centers that accept a specific waste type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|string $wasteType Waste type ID or name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAcceptsWasteType($query, $wasteType)
    {
        return $query->whereHas('wasteTypes', function ($q) use ($wasteType) {
            if (is_numeric($wasteType)) {
                $q->where('waste_types.id', $wasteType);
            } else {
                $q->where('waste_types.name', 'like', "%{$wasteType}%");
            }
        });
    }

    /**
     * Check if the center accepts a specific waste type.
     *
     * @param int|string $wasteTypeId Waste type ID
     * @return bool
     */
    public function acceptsWasteType($wasteTypeId)
    {
        return $this->wasteTypes()->where('waste_types.id', $wasteTypeId)->exists();
    }

    /**
     * Get the material point configuration for a specific material.
     *
     * @param int|string $materialId Material ID
     * @return \App\Models\MaterialPointConfig|null
     */
    public function getMaterialPointConfig($materialId)
    {
        return $this->materialPointConfigs()
            ->where('material_id', $materialId)
            ->first();
    }

    /**
     * Create or update material point configuration.
     *
     * @param int $materialId Material ID
     * @param int $points Points value
     * @param bool $isEnabled Whether the material is enabled
     * @param float $multiplier Optional multiplier for the material
     * @return \App\Models\MaterialPointConfig
     */
    public function setMaterialPointConfig($materialId, $points, $isEnabled, $multiplier = 1.0)
    {
        return $this->materialPointConfigs()->updateOrCreate(
            ['material_id' => $materialId],
            [
                'points' => $points,
                'is_enabled' => $isEnabled,
                'multiplier' => $multiplier,
            ]
        );
    }

    /**
     * Create or update the bonus configuration.
     *
     * @param bool $enabled Whether consecutive days bonus is enabled
     * @param float $bonus Bonus multiplier per consecutive day
     * @param int $maxDays Maximum consecutive days to count
     * @return \App\Models\BonusConfig
     */
    public function setBonusConfig($enabled, $bonus, $maxDays)
    {
        return $this->bonusConfig()->updateOrCreate(
            [],
            [
                'consecutive_days_enabled' => $enabled,
                'consecutive_days_bonus' => $bonus,
                'max_consecutive_days' => $maxDays,
            ]
        );
    }

    /**
     * Get the default hours configuration.
     *
     * @return array
     */
    public static function getDefaultHours()
    {
        return [
            'monday' => '9:00 AM - 5:00 PM',
            'tuesday' => '9:00 AM - 5:00 PM',
            'wednesday' => '9:00 AM - 5:00 PM',
            'thursday' => '9:00 AM - 5:00 PM',
            'friday' => '9:00 AM - 5:00 PM',
            'saturday' => '10:00 AM - 3:00 PM',
            'sunday' => 'Closed',
        ];
    }
}
