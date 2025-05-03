<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\PointsTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RedemptionController extends Controller
{
    /**
     * Redeem a reward.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $rewardId
     * @return \Illuminate\Http\Response
     */
    public function redeem(Request $request, $rewardId)
    {
        $user = Auth::user();
        $reward = Reward::findOrFail($rewardId);

        // Check if reward is available
        if (!$reward->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'This reward is no longer available'
            ], 400);
        }

        // Get user's points balance
        $userBalance = PointsTransaction::getBalance($user->id);

        // Check if user has enough points
        if ($userBalance < $reward->points_cost) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points to redeem this reward'
            ], 400);
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Create redemption
            $redemption = new RewardRedemption([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'status' => RewardRedemption::STATUS_PENDING,
                'points_cost' => $reward->points_cost
            ]);
            $redemption->save();

            // Create points transaction
            $transaction = new PointsTransaction([
                'user_id' => $user->id,
                'points' => $reward->points_cost,
                'type' => PointsTransaction::TYPE_SPENT,
                'category' => PointsTransaction::CATEGORY_REWARD_REDEMPTION,
                'description' => "Redeemed reward: {$reward->title}",
                'center_id' => $reward->center_id,
            ]);

            $redemption->pointsTransaction()->save($transaction);

            DB::commit();

            // Get updated balance
            $newBalance = PointsTransaction::getBalance($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Reward redeemed successfully',
                'data' => [
                    'redemption' => $redemption,
                    'new_balance' => $newBalance
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to redeem reward: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's redemption history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status');

        $query = RewardRedemption::with(['reward', 'reward.center'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $redemptions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $redemptions
        ]);
    }

    /**
     * Get details of a specific redemption.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();

        $redemption = RewardRedemption::with(['reward', 'reward.center'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $redemption
        ]);
    }

    /**
     * Get pending redemptions for center owner to process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPendingRedemptions(Request $request)
    {
        $user = Auth::user();
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a recycling center'
            ], 403);
        }

        $status = $request->input('status', 'pending');

        $query = RewardRedemption::with(['user', 'reward'])
            ->whereHas('reward', function ($q) use ($center) {
                $q->where('center_id', $center->id);
            });

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $redemptions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $redemptions
        ]);
    }

    /**
     * Process a redemption (approve or reject).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function processRedemption(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
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

        $redemption = RewardRedemption::with('reward')
            ->whereHas('reward', function ($q) use ($center) {
                $q->where('center_id', $center->id);
            })
            ->where('id', $id)
            ->first();

        if (!$redemption) {
            return response()->json([
                'success' => false,
                'message' => 'Redemption not found or does not belong to this center'
            ], 404);
        }

        if (!$redemption->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This redemption has already been processed'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $status = $request->status;
            $notes = $request->notes;

            if ($status === 'approved') {
                $redemption->approve($user->id, $notes);
            } else {
                $redemption->reject($user->id, $notes);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Redemption ' . $status . ' successfully',
                'data' => $redemption
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process redemption: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get redemption statistics for a recycling center.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRedemptionStats()
    {
        $user = Auth::user();
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a recycling center'
            ], 403);
        }

        // Get reward IDs for this center
        $rewardIds = Reward::where('center_id', $center->id)->pluck('id');

        // Count redemptions by status
        $pendingCount = RewardRedemption::whereIn('reward_id', $rewardIds)
            ->where('status', RewardRedemption::STATUS_PENDING)
            ->count();

        $approvedCount = RewardRedemption::whereIn('reward_id', $rewardIds)
            ->where('status', RewardRedemption::STATUS_APPROVED)
            ->count();

        $rejectedCount = RewardRedemption::whereIn('reward_id', $rewardIds)
            ->where('status', RewardRedemption::STATUS_REJECTED)
            ->count();

        // Get most popular rewards
        $popularRewards = Reward::whereIn('id', $rewardIds)
            ->withCount(['redemptions' => function ($q) {
                $q->where('status', RewardRedemption::STATUS_APPROVED);
            }])
            ->orderByDesc('redemptions_count')
            ->take(5)
            ->get();

        // Get recent redemptions
        $recentRedemptions = RewardRedemption::with(['user', 'reward'])
            ->whereIn('reward_id', $rewardIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_redemptions' => $pendingCount + $approvedCount + $rejectedCount,
                'pending_count' => $pendingCount,
                'approved_count' => $approvedCount,
                'rejected_count' => $rejectedCount,
                'popular_rewards' => $popularRewards,
                'recent_redemptions' => $recentRedemptions
            ]
        ]);
    }
}
