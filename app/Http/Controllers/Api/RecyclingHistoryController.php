<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassificationHistory;
use App\Models\PointsTransaction;
use App\Models\RecyclingHistory;
use App\Models\User;
use App\Models\WasteItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RecyclingHistoryController extends Controller
{
    /**
     * Get recycling history for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Base query with eager loading of all necessary relationships
            $query = RecyclingHistory::with([
                'wasteItem.wasteType',
                'center',
                'pointsTransaction' // Add points transaction relationship
            ])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Filter by waste type
            if ($request->has('waste_type')) {
                $wasteType = $request->waste_type;
                $query->whereHas('wasteItem.wasteType', function ($q) use ($wasteType) {
                    $q->where('name', $wasteType);
                });
            }

            // Filter by center
            if ($request->has('center_id')) {
                $query->where('center_id', $request->center_id);
            }

            // Filter by date range
            if ($request->has('date_from') && $request->has('date_to')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            } else if ($request->has('date_from')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $query->where('created_at', '>=', $dateFrom);
            } else if ($request->has('date_to')) {
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->where('created_at', '<=', $dateTo);
            }

            // Get pagination parameters
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            // Execute the query with pagination
            $history = $query->paginate($perPage, ['*'], 'page', $page);

            // Format the response with pagination metadata
            return response()->json([
                'success' => true,
                'data' => $history->items(),
                'meta' => [
                    'current_page' => $history->currentPage(),
                    'from' => $history->firstItem(),
                    'last_page' => $history->lastPage(),
                    'path' => $history->path(),
                    'per_page' => $history->perPage(),
                    'to' => $history->lastItem(),
                    'total' => $history->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recycling history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record recycling activity and award points.
     * This is used by recycling center staff when a user recycles items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'waste_item_id' => 'required|exists:waste_items,id',
            'waste_name' => 'required|string',
            'quantity' => 'required|numeric',
            'unit' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        $staffUser = $request->user();
        $center = $staffUser->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'You are not associated with any recycling center'
            ], 403);
        }

        $wasteItem = WasteItem::findOrFail($request->waste_item_id);
        $points = $request->points;

        DB::beginTransaction();

        try {
            // Handle image upload if present
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                // Store the image and get the path
                $imagePath = $image->store('recycling_images', 'public');
            }

            // Create recycling history entry
            $history = RecyclingHistory::create([
                'user_id' => $user->id,
                'center_id' => $center->id,
                'waste_item_id' => $wasteItem->id,
                'waste_name' => $request->waste_name,
                'quantity' => $request->quantity ?? 0,
                'unit' => $request->unit ?? '',
                'image' => $imagePath,
            ]);

            // Create points transaction
            $transaction = new PointsTransaction([
                'user_id' => $user->id,
                'points' => $points,
                'type' => 'earned',
                'category' => 'recycling',
                'description' => "Recycled {$request->quantity} {$request->unit} of {$request->waste_name}",
                'center_id' => $center->id,
            ]);

            $history->pointsTransaction()->save($transaction);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recycling record added successfully',
                'data' => [
                    'history' => $history,
                    'points_earned' => $points,
                    'total_points' => PointsTransaction::getBalance($user->id)
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to record recycling: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific recycling history record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        $history = RecyclingHistory::with(['wasteItem.wasteType', 'center'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    // public function destroy($id)
    // {
    //     $user = request()->user();
    //     $history = RecyclingHistory::where('user_id', $user->id)
    //         ->findOrFail($id);

    //     // Delete image if exists
    //     if ($history->image) {
    //         $imagePath = str_replace('/storage', 'public', $history->image);
    //         Storage::delete($imagePath);
    //     }

    //     $history->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Recycling record deleted successfully'
    //     ]);
    // }

    /**
     * Get recycling statistics for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $totalItems = RecyclingHistory::where('user_id', $user->id)->count();
        $totalPoints = PointsTransaction::where('user_id', $user->id)
            ->where('type', 'earned')
            ->where('category', 'recycling')
            ->sum('points');

        // Get counts by waste type
        $wasteTypeCounts = RecyclingHistory::selectRaw('waste_types.name, COUNT(*) as count')
            ->join('waste_items', 'recycling_histories.waste_item_id', '=', 'waste_items.id')
            ->join('waste_types', 'waste_items.waste_type_id', '=', 'waste_types.id')
            ->where('recycling_histories.user_id', $user->id)
            ->groupBy('waste_types.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        // Get weekly stats
        $weeklyStats = RecyclingHistory::selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, COUNT(*) as count')
            ->where('user_id', $user->id)
            ->groupBy('year', 'week')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'desc')
            ->limit(10)
            ->get();

        $totalClassify = ClassificationHistory::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'totalItems' => $totalItems,
                'totalPoints' => $totalPoints,
                'totalClassify' => $totalClassify->count(),
                'weeklyAverage' => $this->calculateWeeklyAverage($user->id),
                'wasteTypeCounts' => $wasteTypeCounts,
                'weeklyStats' => $weeklyStats,
            ]
        ]);
    }

    /**
     * Calculate weekly average recycling for a user.
     *
     * @param  int  $userId
     * @return float
     */
    private function calculateWeeklyAverage($userId)
    {
        $user = User::find($userId);
        $totalItems = RecyclingHistory::where('user_id', $userId)->count();
        $accountAgeInWeeks = max(1, $user->created_at->diffInWeeks(now()) + 1);

        return round($totalItems / $accountAgeInWeeks, 1);
    }
}
