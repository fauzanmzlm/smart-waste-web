<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusConfig extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'center_id',
        'consecutive_days_enabled',
        'consecutive_days_bonus',
        'max_consecutive_days',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'consecutive_days_enabled' => 'boolean',
        'consecutive_days_bonus' => 'float',
        'max_consecutive_days' => 'integer',
    ];

    /**
     * Get the recycling center that owns the bonus configuration.
     */
    public function center()
    {
        return $this->belongsTo(RecyclingCenter::class, 'center_id');
    }

    /**
     * Calculate bonus points for consecutive days.
     *
     * @param  int  $basePoints
     * @param  int  $consecutiveDays
     * @return int
     */
    public function calculateBonus($basePoints, $consecutiveDays)
    {
        if (!$this->consecutive_days_enabled || $consecutiveDays <= 1) {
            return 0;
        }

        // Cap consecutive days at maximum
        $effectiveDays = min($consecutiveDays, $this->max_consecutive_days);
        
        // Calculate bonus (consecutive days - 1) * bonus rate
        $bonusMultiplier = $this->consecutive_days_bonus * ($effectiveDays - 1);
        
        // Calculate and round to nearest integer
        return (int) round($basePoints * $bonusMultiplier);
    }
}