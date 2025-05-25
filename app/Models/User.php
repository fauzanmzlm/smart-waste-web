<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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
        'loyalty_id',
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

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (is_null($user->loyalty_id)) { // Only generate if not already set
                $user->loyalty_id = static::generateNextLoyaltyId();
            }
        });
    }

    /**
     * Generates the next loyalty ID.
     *
     * @param string $prefix The prefix for the loyalty ID (e.g., "SW").
     * @param int $length The desired length of the numerical part (e.g., 5 for "00001").
     * @return string The next loyalty ID.
     */
    public static function generateNextLoyaltyId(string $prefix = 'SW', int $length = 5): string
    {
        // Find the latest loyalty ID with the given prefix from the users table
        $latestLoyalty = static::where('loyalty_id', 'LIKE', $prefix . '%')
            ->orderBy(DB::raw("SUBSTRING(loyalty_id, " . (strlen($prefix) + 1) . ") + 0"), 'desc')
            ->value('loyalty_id');

        if ($latestLoyalty) {
            // Extract the numeric part
            $numericPart = substr($latestLoyalty, strlen($prefix));
            // Increment the numeric part
            $nextNumericPart = intval($numericPart) + 1;
        } else {
            // If no existing loyalty ID with this prefix, start from 1
            $nextNumericPart = 1;
        }

        // Format the new numeric part with leading zeros
        $newLoyaltyNumber = str_pad($nextNumericPart, $length, '0', STR_PAD_LEFT);

        return $prefix . $newLoyaltyNumber;
    }

    public function recyclingHistories()
    {
        return $this->hasMany(RecyclingHistory::class);
    }

    public function classificationHistories()
    {
        return $this->hasMany(ClassificationHistory::class);
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

    // Get items recycled count
    public function getTotalClassificationsAttribute()
    {
        return $this->classificationHistories->count();
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
