<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'points',
        'type',
        'category',
        'description',
        'transactionable_id',
        'transactionable_type',
        'center_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'points' => 'integer',
    ];

    /**
     * The possible transaction types.
     */
    const TYPE_EARNED = 'earned';
    const TYPE_SPENT = 'spent';

    /**
     * The possible transaction categories.
     */
    const CATEGORY_RECYCLING = 'recycling';
    const CATEGORY_REWARD_REDEMPTION = 'reward_redemption';
    const CATEGORY_BONUS = 'bonus';
    const CATEGORY_REFUND = 'refund';
    const CATEGORY_TRANSFER = 'transfer';
    const CATEGORY_OTHER = 'other';

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recycling center associated with the transaction.
     */
    public function center()
    {
        return $this->belongsTo(RecyclingCenter::class, 'center_id');
    }

    /**
     * Get the model that the transaction is for.
     */
    public function transactionable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include earned transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEarned($query)
    {
        return $query->where('type', self::TYPE_EARNED);
    }

    /**
     * Scope a query to only include spent transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpent($query)
    {
        return $query->where('type', self::TYPE_SPENT);
    }

    /**
     * Scope a query to only include transactions for a specific category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include transactions for a specific user.
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
     * Scope a query to only include transactions for a specific center.
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
     * Scope a query to only include transactions within a specific date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the total points earned by a user.
     *
     * @param  int  $userId
     * @return int
     */
    public static function getTotalEarned($userId)
    {
        return self::forUser($userId)->earned()->sum('points');
    }

    /**
     * Get the total points spent by a user.
     *
     * @param  int  $userId
     * @return int
     */
    public static function getTotalSpent($userId)
    {
        return self::forUser($userId)->spent()->sum('points');
    }

    /**
     * Get the current points balance for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public static function getBalance($userId)
    {
        $earned = self::getTotalEarned($userId);
        $spent = self::getTotalSpent($userId);
        
        return $earned - $spent;
    }

    /**
     * Get points summary by category for a user.
     *
     * @param  int  $userId
     * @param  string|null  $timeframe  (all, week, month, year)
     * @return array
     */
    public static function getSummaryByCategory($userId, $timeframe = 'all')
    {
        $query = self::forUser($userId);
        
        // Apply timeframe filter
        if ($timeframe !== 'all') {
            $startDate = now();
            
            switch ($timeframe) {
                case 'week':
                    $startDate = $startDate->subWeek();
                    break;
                case 'month':
                    $startDate = $startDate->subMonth();
                    break;
                case 'year':
                    $startDate = $startDate->subYear();
                    break;
            }
            
            $query->where('created_at', '>=', $startDate);
        }
        
        // Group by category and type
        $results = $query->selectRaw('category, type, SUM(points) as total_points')
            ->groupBy('category', 'type')
            ->get();
        
        // Format the results
        $summary = [];
        
        foreach ($results as $result) {
            $points = (int) $result->total_points;
            
            if (!isset($summary[$result->category])) {
                $summary[$result->category] = 0;
            }
            
            if ($result->type === self::TYPE_EARNED) {
                $summary[$result->category] += $points;
            } else {
                $summary[$result->category] -= $points;
            }
        }
        
        return $summary;
    }

    /**
     * Get points leaderboard.
     *
     * @param  int  $limit
     * @param  string|null  $timeframe  (all, week, month, year)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLeaderboard($limit = 10, $timeframe = 'all')
    {
        $query = self::selectRaw('user_id, 
                           SUM(CASE WHEN type = ? THEN points ELSE 0 END) as earned, 
                           SUM(CASE WHEN type = ? THEN points ELSE 0 END) as spent, 
                           (SUM(CASE WHEN type = ? THEN points ELSE 0 END) - 
                            SUM(CASE WHEN type = ? THEN points ELSE 0 END)) as balance', 
                            [self::TYPE_EARNED, self::TYPE_SPENT, self::TYPE_EARNED, self::TYPE_SPENT])
                  ->groupBy('user_id')
                  ->orderByDesc('balance')
                  ->limit($limit);
        
        // Apply timeframe filter
        if ($timeframe !== 'all') {
            $startDate = now();
            
            switch ($timeframe) {
                case 'week':
                    $startDate = $startDate->subWeek();
                    break;
                case 'month':
                    $startDate = $startDate->subMonth();
                    break;
                case 'year':
                    $startDate = $startDate->subYear();
                    break;
            }
            
            $query->where('created_at', '>=', $startDate);
        }
        
        $results = $query->get();
        
        // Load user data for each result
        foreach ($results as $result) {
            $result->user = User::find($result->user_id);
        }
        
        return $results;
    }
}