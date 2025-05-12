<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointsTransaction;
use App\Models\MaterialPointConfig;
use App\Models\Material;
use App\Models\RecyclingHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PointsController extends Controller
{
    /**
     * Get the user's points balance.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBalance()
    {
        $user = Auth::user();
        $balance = PointsTransaction::getBalance($user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $balance
            ]
        ]);
    }

    /**
     * Get the user's points history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getHistory(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);
        $type = $request->input('type');
        $category = $request->input('category');

        $query = PointsTransaction::with('center')
            ->forUser($user->id)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        if ($category) {
            $query->where('category', $category);
        }

        $transactions = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]
        ]);
    }

    /**
     * Get details of a specific transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getTransactionDetails($id)
    {
        $user = Auth::user();

        $transaction = PointsTransaction::with(['transactionable', 'center'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Get user's points summary by category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSummary(Request $request)
    {
        $user = Auth::user();
        $timeframe = $request->input('timeframe', 'all');

        $summary = PointsTransaction::getSummaryByCategory($user->id, $timeframe);

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Award points to a user for recycling a material.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awardRecyclingPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a recycling center'
            ], 403);
        }

        $materialId = $request->material_id;
        $quantity = $request->quantity;

        // Get the user who recycled
        $recyclingUser = User::findOrFail($recyclingHistory->user_id);

        // Get material points configuration for this center
        $materialConfig = MaterialPointConfig::where('center_id', $center->id)
            ->where('material_id', $materialId)
            ->first();

        if (!$materialConfig) {
            // Use default points if no specific configuration exists
            $material = Material::findOrFail($materialId);
            $pointsPerUnit = $material->default_points;
            $isEnabled = true;
        } else {
            $pointsPerUnit = $materialConfig->points;
            $isEnabled = $materialConfig->is_enabled;
        }

        if (!$isEnabled) {
            return response()->json([
                'success' => false,
                'message' => 'This material is not currently enabled for points'
            ], 400);
        }

        // Calculate base points
        $basePoints = (int) round($pointsPerUnit * $quantity);

        // // Check for consecutive days bonus
        // $bonusPoints = 0;
        // $consecutiveDays = 1;
        // $bonusConfig = $center->bonusConfig;

        // if ($bonusConfig && $bonusConfig->consecutive_days_enabled) {
        //     // Get user's recycling history for the past days
        //     $now = Carbon::now();
        //     $recentHistory = RecyclingHistory::where('user_id', $recyclingUser->id)
        //         ->where('center_id', $center->id)
        //         ->where('created_at', '<', $now->startOfDay())
        //         ->orderBy('created_at', 'desc')
        //         ->get()
        //         ->groupBy(function ($date) {
        //             return Carbon::parse($date->created_at)->format('Y-m-d');
        //         });

        //     // Count consecutive days up to the maximum
        //     $checkDate = Carbon::now()->subDay();
        //     $maxDays = $bonusConfig->max_consecutive_days;

        //     while ($consecutiveDays < $maxDays) {
        //         $dateKey = $checkDate->format('Y-m-d');

        //         if (isset($recentHistory[$dateKey])) {
        //             $consecutiveDays++;
        //             $checkDate->subDay();
        //         } else {
        //             break;
        //         }
        //     }

        //     // Calculate bonus if consecutive days > 1
        //     if ($consecutiveDays > 1) {
        //         $bonusMultiplier = $bonusConfig->consecutive_days_bonus * ($consecutiveDays - 1);
        //         $bonusPoints = (int) round($basePoints * $bonusMultiplier);
        //     }
        // }

        // Total points to award
        $totalPoints = $basePoints;

        DB::beginTransaction();

        try {
            // Create main points transaction
            $transaction = new PointsTransaction([
                'user_id' => $recyclingUser->id,
                'points' => $basePoints,
                'type' => PointsTransaction::TYPE_EARNED,
                'category' => PointsTransaction::CATEGORY_RECYCLING,
                'description' => "Recycled {$quantity} {$recyclingHistory->material->name}",
                'center_id' => $center->id,
            ]);

            $recyclingHistory->pointsTransaction()->save($transaction);

            // Create bonus transaction if applicable
            if ($bonusPoints > 0) {
                $bonusTransaction = new PointsTransaction([
                    'user_id' => $recyclingUser->id,
                    'points' => $bonusPoints,
                    'type' => PointsTransaction::TYPE_EARNED,
                    'category' => PointsTransaction::CATEGORY_BONUS,
                    'description' => "Bonus for {$consecutiveDays} consecutive days of recycling",
                    'center_id' => $center->id,
                ]);

                $recyclingUser->pointsTransactions()->save($bonusTransaction);
            }

            // Update recycling history with points
            $recyclingHistory->points_earned = $totalPoints;
            $recyclingHistory->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Points awarded successfully',
                'data' => [
                    'base_points' => $basePoints,
                    'bonus_points' => $bonusPoints,
                    'total_points' => $totalPoints,
                    'consecutive_days' => $consecutiveDays
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to award points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Award points manually to a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function awardPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|in:recycling,bonus,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a recycling center'
            ], 403);
        }

        $targetUser = User::findOrFail($request->user_id);
        $points = $request->points;
        $description = $request->description;
        $category = $request->input('category', 'other');

        // Create the transaction
        $transaction = new PointsTransaction([
            'user_id' => $targetUser->id,
            'points' => $points,
            'type' => PointsTransaction::TYPE_EARNED,
            'category' => $category,
            'description' => $description,
            'center_id' => $center->id,
        ]);

        $transaction->save();

        return response()->json([
            'success' => true,
            'message' => 'Points awarded successfully',
            'data' => $transaction
        ]);
    }

    /**
     * Get materials points rates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getMaterialPointsRates(Request $request)
    {
        $centerId = $request->input('center_id');

        // If center_id is provided, get rates for that center
        if ($centerId) {
            $materialConfigs = MaterialPointConfig::with('material')
                ->where('center_id', $centerId)
                ->where('is_enabled', true)
                ->get();

            $rates = $materialConfigs->map(function ($config) {
                return [
                    'material_id' => $config->material_id,
                    'material_name' => $config->material->name,
                    'points' => $config->points,
                    'icon' => $config->material->icon,
                ];
            });
        } else {
            // Otherwise get default rates
            $materials = Material::all();

            $rates = $materials->map(function ($material) {
                return [
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'points' => $material->default_points,
                    'icon' => $material->icon,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => $rates
        ]);
    }

    /**
     * Configure points for materials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function configureMaterialPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'materials' => 'required|array',
            'materials.*.id' => 'required|exists:materials,id',
            'materials.*.points' => 'required|integer|min:0',
            'materials.*.enabled' => 'required|boolean',
            'global_multiplier' => 'nullable|numeric|min:0.1',
            'bonus_config' => 'nullable|array',
            'bonus_config.consecutive_days_enabled' => 'nullable|boolean',
            'bonus_config.consecutive_days_bonus' => 'nullable|numeric|min:0.1',
            'bonus_config.max_consecutive_days' => 'nullable|integer|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a recycling center'
            ], 403);
        }

        try {
            // Configure materials
            MaterialPointConfig::configureMaterials(
                $center->id,
                $request->materials
            );

            // Update center's global multiplier if provided
            if ($request->has('global_multiplier')) {
                $center->points_multiplier = $request->global_multiplier;
            }

            // Update bonus configuration if provided
            // if ($request->has('bonus_config')) {
            //     $bonusConfig = $request->bonus_config;

            //     // Create or update bonus config
            //     $center->bonusConfig()->updateOrCreate(
            //         ['center_id' => $center->id],
            //         [
            //             'consecutive_days_enabled' => $bonusConfig['consecutive_days_enabled'] ?? false,
            //             'consecutive_days_bonus' => $bonusConfig['consecutive_days_bonus'] ?? 0.5,
            //             'max_consecutive_days' => $bonusConfig['max_consecutive_days'] ?? 5,
            //         ]
            //     );
            // }

            $center->save();

            return response()->json([
                'success' => true,
                'message' => 'Points configuration updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update points configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get points statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPointsStatistics()
    {
        $user = Auth::user();
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a recycling center'
            ], 403);
        }

        // Total points awarded by this center
        $totalPointsAwarded = PointsTransaction::where('center_id', $center->id)
            ->where('type', PointsTransaction::TYPE_EARNED)
            ->sum('points');

        // Points awarded by category
        $pointsByCategory = PointsTransaction::where('center_id', $center->id)
            ->where('type', PointsTransaction::TYPE_EARNED)
            ->selectRaw('category, SUM(points) as total')
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category')
            ->toArray();

        // Points awarded by month (last 6 months)
        $pointsByMonth = PointsTransaction::where('center_id', $center->id)
            ->where('type', PointsTransaction::TYPE_EARNED)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(points) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                    'total' => $item->total,
                ];
            });

        // Top users by points earned from this center
        $topUsers = PointsTransaction::where('center_id', $center->id)
            ->where('type', PointsTransaction::TYPE_EARNED)
            ->selectRaw('user_id, SUM(points) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'user_id' => $item->user_id,
                    'name' => $user ? $user->name : 'Unknown User',
                    'total_points' => $item->total,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_points_awarded' => $totalPointsAwarded,
                'points_by_category' => $pointsByCategory,
                'points_by_month' => $pointsByMonth,
                'top_users' => $topUsers,
            ]
        ]);
    }

    /**
     * Get the points leaderboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLeaderboard(Request $request)
    {
        $limit = $request->input('limit', 10);
        $timeframe = $request->input('timeframe', 'all');

        $leaderboard = PointsTransaction::getLeaderboard($limit, $timeframe);

        return response()->json([
            'success' => true,
            'data' => $leaderboard
        ]);
    }
}
