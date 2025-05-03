<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RewardRedemption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'reward_id',
        'code',
        'status',
        'notes',
        'points_cost',
        'processed_at',
        'processed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'points_cost' => 'integer',
        'processed_at' => 'datetime',
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = [
        'user_name',
        'reward_title',
    ];

    /**
     * The possible statuses for a redemption.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate a unique redemption code when creating
        static::creating(function ($redemption) {
            if (empty($redemption->code)) {
                $redemption->code = strtoupper(Str::random(8));
                
                // Ensure the code is unique
                while (static::where('code', $redemption->code)->exists()) {
                    $redemption->code = strtoupper(Str::random(8));
                }
            }
        });
    }

    /**
     * Get the user that redeemed the reward.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward that was redeemed.
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Get the user that processed the redemption.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the related points transaction.
     */
    public function pointsTransaction()
    {
        return $this->morphOne(PointsTransaction::class, 'transactionable');
    }

    /**
     * Get the user name.
     *
     * @return string|null
     */
    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : null;
    }

    /**
     * Get the reward title.
     *
     * @return string|null
     */
    public function getRewardTitleAttribute()
    {
        return $this->reward ? $this->reward->title : null;
    }

    /**
     * Scope a query to only include pending redemptions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved redemptions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include rejected redemptions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope a query to only include redemptions for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include redemptions for a specific center.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $centerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCenter($query, $centerId)
    {
        return $query->whereHas('reward', function ($query) use ($centerId) {
            $query->where('center_id', $centerId);
        });
    }

    /**
     * Mark the redemption as approved.
     *
     * @param  int  $processedBy  User ID of the approver
     * @param  string|null  $notes  Optional notes
     * @return bool
     */
    public function approve($processedBy, $notes = null)
    {
        $this->status = self::STATUS_APPROVED;
        $this->processed_at = now();
        $this->processed_by = $processedBy;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        return $this->save();
    }

    /**
     * Mark the redemption as rejected.
     *
     * @param  int  $processedBy  User ID of the rejector
     * @param  string|null  $notes  Optional notes
     * @return bool
     */
    public function reject($processedBy, $notes = null)
    {
        $this->status = self::STATUS_REJECTED;
        $this->processed_at = now();
        $this->processed_by = $processedBy;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        // Refund the points to the user
        if ($this->pointsTransaction) {
            // Create a new transaction to refund the points
            $refundTransaction = new PointsTransaction([
                'user_id' => $this->user_id,
                'points' => $this->points_cost, // Positive for refund
                'type' => 'earned',
                'category' => 'refund',
                'description' => "Refund for rejected reward: {$this->reward_title}",
            ]);
            
            $this->user->pointsTransactions()->save($refundTransaction);
        }
        
        return $this->save();
    }

    /**
     * Check if the redemption is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the redemption is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the redemption is rejected.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }
}