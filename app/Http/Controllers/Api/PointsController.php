<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointsTransaction;
use App\Models\MaterialPointConfig;
use App\Models\Material;
use App\Models\RecyclingHistory;
use App\Models\User;
use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    // /**
    //  * Award points manually to a user.
    //  * This is used by recycling center staff.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function awardPoints(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required|exists:users,id',
    //         'points' => 'required|integer|min:1',
    //         'description' => 'required|string|max:255',
    //         'category' => 'nullable|string|in:recycling,bonus,other',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation errors',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $staffUser = Auth::user();
    //     $center = $staffUser->recyclingCenter;

    //     if (!$center) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'User is not associated with a recycling center'
    //         ], 403);
    //     }

    //     $targetUser = User::findOrFail($request->user_id);
    //     $points = $request->points;
    //     $description = $request->description;
    //     $category = $request->input('category', 'other');

    //     // Create the transaction
    //     $transaction = new PointsTransaction([
    //         'user_id' => $targetUser->id,
    //         'points' => $points,
    //         'type' => 'earned',
    //         'category' => $category,
    //         'description' => $description,
    //         'center_id' => $center->id,
    //     ]);

    //     $transaction->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Points awarded successfully',
    //         'data' => [
    //             'transaction' => $transaction,
    //             'new_balance' => PointsTransaction::getBalance($targetUser->id)
    //         ]
    //     ]);
    // }

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

    /**
     * Record recycling activities with multiple items and award points
     * Support both predefined and manual items
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recyclingWithPoints(Request $request)
    {
        // For debugging - log the request data
        Log::info('Recycling request data:', $request->all());

        // Special log for items
        if ($request->has('items')) {
            Log::info('Items data type: ' . gettype($request->items));
            if (is_string($request->items)) {
                Log::info('Items JSON: ' . $request->items);
            }
        }

        // Validate basic information
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get items from request
        $items = [];
        if ($request->has('items')) {
            // If items are in JSON format (from FormData)
            if (is_string($request->items)) {
                try {
                    $items = json_decode($request->items, true);

                    // Check if JSON decoding failed
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('JSON decode error: ' . json_last_error_msg());
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid JSON in items: ' . json_last_error_msg()
                        ], 422);
                    }
                } catch (\Exception $e) {
                    Log::error('Exception decoding items JSON: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error parsing items: ' . $e->getMessage()
                    ], 422);
                }
            } else {
                // If items are already in array format
                $items = $request->items;
            }
        }

        // Validate items
        if (empty($items) || !is_array($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No recycling items provided or invalid format'
            ], 422);
        }

        // Get the staff user and center
        $staffUser = Auth::user();
        $center = $staffUser->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'You are not associated with a recycling center'
            ], 403);
        }

        $user = User::findOrFail($request->user_id);
        $imagePath = null;
        $totalPoints = 0;
        $histories = [];

        DB::beginTransaction();

        try {
            // Handle image upload if present - single image for the whole transaction
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                // Store the image and get the path
                $imagePath = $image->store('recycling_images', 'public');
                Log::info('Image stored at: ' . $imagePath);
            }

            // Process each item
            foreach ($items as $item) {
                // Validate each item
                if (empty($item['waste_name']) || !isset($item['quantity']) || empty($item['unit'])) {
                    Log::error('Invalid item data: ' . json_encode($item));
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid item data provided'
                    ], 422);
                }

                // Calculate points
                $points = 0;
                $wasteItemId = null;

                // Handle waste_item_id conversion from string to int or null
                $itemId = $item['waste_item_id'] ?? null;
                if (
                    $itemId && is_string($itemId) &&
                    (strpos($itemId, 'manual_') === 0 || $itemId === 'manual')
                ) {
                    // Manual item - just keep waste_item_id as null
                    $wasteItemId = null;
                } elseif ($itemId && is_numeric($itemId)) {
                    // Valid waste item ID - convert to int
                    $wasteItemId = (int)$itemId;
                }

                // Predefined waste item
                if ($wasteItemId !== null) {
                    $wasteItem = WasteItem::find($wasteItemId);

                    if ($wasteItem) {
                        $wasteItemId = $wasteItem->id;
                        $points = $wasteItem->points * $item['quantity'];
                    } else {
                        // If waste_item_id is provided but invalid, treat as manual
                        Log::warning("Invalid waste_item_id: $wasteItemId - treating as manual entry");
                        $wasteItemId = null;
                        $points = isset($item['totalPoints']) ? $item['totalPoints'] : (isset($item['points']) ? $item['points'] * $item['quantity'] : 0);
                    }
                }
                // Manual waste item
                else {
                    $points = isset($item['totalPoints']) ? $item['totalPoints'] : (isset($item['points']) ? $item['points'] * $item['quantity'] : 0);
                }

                $totalPoints += $points;
                Log::info("Adding $points points for item {$item['waste_name']}");

                // Create recycling history entry
                $history = RecyclingHistory::create([
                    'user_id' => $user->id,
                    'center_id' => $center->id,
                    'waste_item_id' => $wasteItemId,
                    'waste_name' => $item['waste_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'image' => $imagePath, // Same image for all items
                ]);

                $histories[] = $history;

                // Create points transaction for the item
                $transaction = new PointsTransaction([
                    'user_id' => $user->id,
                    'points' => $points,
                    'type' => 'earned',
                    'category' => 'recycling',
                    'description' => "Recycled {$item['quantity']} {$item['unit']} of {$item['waste_name']}",
                    'center_id' => $center->id,
                ]);

                $history->pointsTransaction()->save($transaction);
            }

            DB::commit();
            Log::info("Successfully awarded $totalPoints points to user {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Recycling recorded and points awarded successfully',
                'data' => [
                    'histories' => $histories,
                    'total_points_earned' => $totalPoints,
                    'new_balance' => PointsTransaction::getBalance($user->id),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ],
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception in recyclingWithPoints: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Clean up the uploaded image if something went wrong
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to record recycling: ' . $e->getMessage()
            ], 500);
        }
    }
}
