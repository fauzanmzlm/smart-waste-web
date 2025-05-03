<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * The "type" of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        // Check cache first
        if (Cache::has('setting_' . $key)) {
            return Cache::get('setting_' . $key);
        }
        
        // Get from database
        $setting = self::find($key);
        
        if (!$setting) {
            return $default;
        }
        
        // Cache the result
        Cache::put('setting_' . $key, $setting->value, 60 * 24); // Cache for 24 hours
        
        return $setting->value;
    }
    
    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        // Update or create the setting
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        
        // Update the cache
        Cache::put('setting_' . $key, $value, 60 * 24); // Cache for 24 hours
    }
    
    /**
     * Get all settings as an array.
     *
     * @return array
     */
    public static function getAll()
    {
        // Check cache first
        if (Cache::has('settings')) {
            return Cache::get('settings');
        }
        
        // Get all settings from the database
        $settings = self::all()->pluck('value', 'key')->toArray();
        
        // Cache the results
        Cache::put('settings', $settings, 60 * 24); // Cache for 24 hours
        
        return $settings;
    }
    
    /**
     * Clear the settings cache.
     *
     * @return void
     */
    public static function clearCache()
    {
        Cache::forget('settings');
        
        // Clear individual setting caches
        $settings = self::all();
        
        foreach ($settings as $setting) {
            Cache::forget('setting_' . $setting->key);
        }
    }
}