<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'location',
        'bio',
        'photoURL',
        'last_login_at',
        'account_type',
        'subscription_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function recyclingHistories()
    {
        return $this->hasMany(RecyclingHistory::class);
    }

    public function greenPoints()
    {
        return $this->hasMany(GreenPoint::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withTimestamps()
            ->withPivot('earned_at');
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    // Calculate total green points
    public function getTotalPointsAttribute()
    {
        return $this->greenPoints->sum('points');
    }

    // Get items recycled count
    public function getItemsRecycledAttribute()
    {
        return $this->recyclingHistories->count();
    }

    /**
     * Get the points transactions for the user.
     */
    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class);
    }

    /**
     * Get the reward redemptions for the user.
     */
    public function rewardRedemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Get the recycling center owned by the user.
     */
    public function recyclingCenter()
    {
        return $this->hasOne(RecyclingCenter::class, 'user_id');
    }

    /**
     * Get the user's points balance.
     *
     * @return int
     */
    public function getPointsBalanceAttribute()
    {
        return PointsTransaction::getBalance($this->id);
    }

    /**
     * Check if the user is a recycling center owner.
     *
     * @return bool
     */
    public function isCenterOwner()
    {
        return $this->recyclingCenter()->exists();
    }
}
