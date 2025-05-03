<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GreenPoint;
use App\Models\RecyclingHistory;
use App\Models\User;
use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RecyclingHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = RecyclingHistory::with(['wasteItem.wasteType'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by waste type
        if ($request->has('waste_type')) {
            $wasteType = $request->waste_type;
            $query->whereHas('wasteItem.wasteType', function ($q) use ($wasteType) {
                $q->where('name', $wasteType);
            });
        }

        $history = $query->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'waste_item_id' => 'required|exists:waste_items,id',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $wasteItem = WasteItem::findOrFail($request->waste_item_id);
        $points = $wasteItem->points;

        // Handle image upload if provided
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/recycling_history');
            $imageUrl = Storage::url($imagePath);
        }

        // Create recycling history entry
        $history = RecyclingHistory::create([
            'user_id' => $user->id,
            'waste_item_id' => $wasteItem->id,
            'location' => $request->location,
            'image' => $imageUrl,
            'points_earned' => $points,
        ]);

        // Award green points
        GreenPoint::create([
            'user_id' => $user->id,
            'points' => $points,
            'source' => 'recycling',
            'source_id' => $history->id,
            'description' => "Recycled {$wasteItem->name}"
        ]);

        // Check for badge achievements (would be implemented in a real app)
        // $this->checkForBadgeAchievements($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Recycling record added successfully',
            'data' => $history,
            'points_earned' => $points
        ], 201);
    }

    public function show($id)
    {
        $user = request()->user();
        $history = RecyclingHistory::with(['wasteItem.wasteType'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    public function destroy($id)
    {
        $user = request()->user();
        $history = RecyclingHistory::where('user_id', $user->id)
            ->findOrFail($id);

        // Delete image if exists
        if ($history->image) {
            $imagePath = str_replace('/storage', 'public', $history->image);
            Storage::delete($imagePath);
        }

        // Delete associated green points
        GreenPoint::where('source', 'recycling')
            ->where('source_id', $history->id)
            ->delete();

        $history->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recycling record deleted successfully'
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();

        $totalItems = $user->recyclingHistories()->count();
        $totalPoints = $user->greenPoints()->sum('points');

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

        return response()->json([
            'success' => true,
            'data' => [
                'totalItems' => $totalItems,
                'totalPoints' => $totalPoints,
                'weeklyAverage' => $this->calculateWeeklyAverage($user->id),
                'wasteTypeCounts' => $wasteTypeCounts,
                'weeklyStats' => $weeklyStats,
            ]
        ]);
    }

    private function calculateWeeklyAverage($userId)
    {
        $user = User::find($userId);
        $totalItems = $user->recyclingHistories()->count();
        $accountAgeInWeeks = max(1, $user->created_at->diffInWeeks(now()) + 1);

        return round($totalItems / $accountAgeInWeeks, 1);
    }
}
