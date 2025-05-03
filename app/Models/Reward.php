<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reward extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'center_id',
        'title',
        'description',
        'category',
        'points_cost',
        'quantity',
        'expiry_date',
        'image',
        'terms',
        'redemption_instructions',
        'is_active',
        'is_featured',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'points_cost' => 'integer',
        'quantity' => 'integer',
        'expiry_date' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = [
        'redemption_count',
        'center_name',
    ];

    /**
     * Get the recycling center that owns the reward.
     */
    public function center()
    {
        return $this->belongsTo(RecyclingCenter::class, 'center_id');
    }

    /**
     * Get the redemptions for the reward.
     */
    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Get the number of redemptions.
     *
     * @return int
     */
    public function getRedemptionCountAttribute()
    {
        return $this->redemptions()->count();
    }

    /**
     * Get the center name.
     *
     * @return string
     */
    public function getCenterNameAttribute()
    {
        return $this->center ? $this->center->name : null;
    }

    /**
     * Scope a query to only include active rewards.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include rewards that have not expired.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
        });
    }

    /**
     * Scope a query to only include rewards that have available quantity.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('quantity')
                  ->orWhereRaw('quantity > (SELECT COUNT(*) FROM reward_redemptions WHERE reward_id = rewards.id AND status != "rejected")');
        });
    }

    /**
     * Scope a query to only include featured rewards.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include rewards for a specific recycling center.
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
     * Check if the reward is available for redemption.
     *
     * @return bool
     */
    public function isAvailable()
    {
        // Check if reward is active
        if (!$this->is_active) {
            return false;
        }

        // Check if reward has expired
        if ($this->expiry_date && $this->expiry_date < now()) {
            return false;
        }

        // Check if reward has available quantity
        if ($this->quantity !== null) {
            $usedQuantity = $this->redemptions()->whereIn('status', ['pending', 'approved'])->count();
            if ($usedQuantity >= $this->quantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get remaining quantity of the reward.
     *
     * @return int|null
     */
    public function getRemainingQuantity()
    {
        if ($this->quantity === null) {
            return null; // Unlimited
        }

        $usedQuantity = $this->redemptions()->whereIn('status', ['pending', 'approved'])->count();
        return max(0, $this->quantity - $usedQuantity);
    }
}