<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialPointConfig extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'center_id',
        'material_id',
        'points',
        'is_enabled',
        'multiplier',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'points' => 'integer',
        'is_enabled' => 'boolean',
        'multiplier' => 'float',
    ];

    /**
     * Get the recycling center that owns the configuration.
     */
    public function center()
    {
        return $this->belongsTo(RecyclingCenter::class, 'center_id');
    }

    /**
     * Get the material that is configured.
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    /**
     * Scope a query to only include enabled configurations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope a query to only include configurations for a specific center.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $centerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCenter($query, $centerId)
    {
        return $query->where('center_id', $centerId);
    }

    /**
     * Scope a query to only include configurations for a specific material.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $materialId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMaterial($query, $materialId)
    {
        return $query->where('material_id', $materialId);
    }

    /**
     * Calculate the effective points for a material with multipliers applied.
     *
     * @param  float|null  $globalMultiplier  Optional global multiplier to apply
     * @return int
     */
    public function getEffectivePoints($globalMultiplier = null)
    {
        if (!$this->is_enabled) {
            return 0;
        }
        
        $points = $this->points;
        
        // Apply material-specific multiplier if set
        if ($this->multiplier !== null && $this->multiplier > 0) {
            $points = $points * $this->multiplier;
        }
        
        // Apply global multiplier if provided
        if ($globalMultiplier !== null && $globalMultiplier > 0) {
            $points = $points * $globalMultiplier;
        }
        
        // Round to nearest integer
        return (int) round($points);
    }

    /**
     * Create or update material point configurations for a center.
     *
     * @param  int  $centerId
     * @param  array  $materials
     * @return void
     */
    public static function configureMaterials($centerId, $materials)
    {
        foreach ($materials as $material) {
            self::updateOrCreate(
                [
                    'center_id' => $centerId,
                    'material_id' => $material['id'],
                ],
                [
                    'points' => $material['points'],
                    'is_enabled' => $material['enabled'],
                    'multiplier' => $material['multiplier'] ?? 1.0,
                ]
            );
        }
    }

    /**
     * Get all material point configurations for a center, with default values for missing materials.
     *
     * @param  int  $centerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getConfigsWithDefaults($centerId)
    {
        // Get all materials
        $materials = Material::all();
        
        // Get existing configurations
        $existingConfigs = self::forCenter($centerId)->get()->keyBy('material_id');
        
        // Create a collection with default values for missing configurations
        $configs = collect();
        
        foreach ($materials as $material) {
            if (isset($existingConfigs[$material->id])) {
                $configs->push($existingConfigs[$material->id]);
            } else {
                // Create a new instance with default values
                $config = new self([
                    'center_id' => $centerId,
                    'material_id' => $material->id,
                    'points' => $material->default_points,
                    'is_enabled' => true,
                    'multiplier' => 1.0,
                ]);
                
                $config->material = $material;
                $configs->push($config);
            }
        }
        
        return $configs;
    }
}