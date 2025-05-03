<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'icon',
        'default_points',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'default_points' => 'integer',
    ];

    /**
     * Get the point configurations for this material.
     */
    public function pointConfigs()
    {
        return $this->hasMany(MaterialPointConfig::class, 'material_id');
    }

    /**
     * Get the recycling history entries for this material.
     */
    public function recyclingHistories()
    {
        return $this->hasMany(RecyclingHistory::class, 'material_id');
    }

    /**
     * Scope a query to only include materials with a specific search term.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('description', 'like', "%{$search}%");
    }

    /**
     * Get the default materials with their configurations.
     *
     * @return array
     */
    public static function getDefaultMaterials()
    {
        return [
            [
                'name' => 'Plastic',
                'description' => 'Plastic waste includes bottles, packaging, and containers.',
                'icon' => '🧴',
                'default_points' => 5,
            ],
            [
                'name' => 'Paper',
                'description' => 'Paper waste includes newspapers, cardboard, and packaging.',
                'icon' => '📄',
                'default_points' => 3,
            ],
            [
                'name' => 'Glass',
                'description' => 'Glass waste includes bottles, jars, and containers.',
                'icon' => '🥛',
                'default_points' => 4,
            ],
            [
                'name' => 'Metal',
                'description' => 'Metal waste includes cans, aluminum, and steel products.',
                'icon' => '🥫',
                'default_points' => 6,
            ],
            [
                'name' => 'Electronics',
                'description' => 'Electronic waste includes old devices, batteries, and cables.',
                'icon' => '💻',
                'default_points' => 10,
            ],
            [
                'name' => 'Batteries',
                'description' => 'Battery waste includes alkaline, lithium, and rechargeable batteries.',
                'icon' => '🔋',
                'default_points' => 8,
            ],
            [
                'name' => 'Organic',
                'description' => 'Organic waste includes food scraps, yard waste, and compostables.',
                'icon' => '🌱',
                'default_points' => 2,
            ],
            [
                'name' => 'Textiles',
                'description' => 'Textile waste includes clothing, fabrics, and materials.',
                'icon' => '👕',
                'default_points' => 5,
            ],
            [
                'name' => 'Hazardous',
                'description' => 'Hazardous waste includes chemicals, paints, and toxic materials.',
                'icon' => '☢️',
                'default_points' => 12,
            ],
        ];
    }

    /**
     * Seed the default materials into the database.
     *
     * @return void
     */
    public static function seedDefaults()
    {
        $defaultMaterials = self::getDefaultMaterials();
        
        foreach ($defaultMaterials as $material) {
            self::updateOrCreate(
                ['name' => $material['name']],
                [
                    'description' => $material['description'],
                    'icon' => $material['icon'],
                    'default_points' => $material['default_points'],
                ]
            );
        }
    }
}