<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RecyclingCenter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RewardController extends Controller
{
    /**
     * Get all available rewards for users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get query parameters
        $centerId = $request->input('center_id');
        $category = $request->input('category');
        $search = $request->input('search');
        $minPoints = $request->input('min_points');
        $maxPoints = $request->input('max_points');
        $sort = $request->input('sort', 'newest');
        $featured = $request->input('featured', false);

        // Start with active, non-expired, available rewards
        $query = Reward::active()->notExpired()->available()->with('center');

        // Apply filters
        if ($centerId) {
            $query->where('center_id', $centerId);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('center', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($minPoints) {
            $query->where('points_cost', '>=', $minPoints);
        }

        if ($maxPoints) {
            $query->where('points_cost', '<=', $maxPoints);
        }

        if ($featured) {
            $query->featured();
        }

        // Apply sorting
        switch ($sort) {
            case 'price_low':
                $query->orderBy('points_cost', 'asc');
                break;
            case 'price_high':
                $query->orderBy('points_cost', 'desc');
                break;
            case 'popular':
                $query->withCount(['redemptions' => function($q) {
                    $q->where('status', 'approved');
                }])->orderByDesc('redemptions_count');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $rewards = $query->get();

        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }

    /**
     * Get a specific reward by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reward = Reward::with('center')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $reward
        ]);
    }

    /**
     * Get featured rewards.
     *
     * @return \Illuminate\Http\Response
     */
    public function featured()
    {
        $rewards = Reward::active()
            ->notExpired()
            ->available()
            ->featured()
            ->with('center')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }

    /**
     * Get rewards created by the authenticated center owner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function centerRewards(Request $request)
    {
        $user = Auth::user();
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a recycling center'
            ], 403);
        }

        $status = $request->input('status');
        $query = Reward::where('center_id', $center->id);

        if ($status) {
            switch ($status) {
                case 'active':
                    $query->active()->notExpired();
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'expired':
                    $query->active()->whereNotNull('expiry_date')->where('expiry_date', '<', now());
                    break;
            }
        }

        $rewards = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }

    /**
     * Create a new reward.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'points_cost' => 'required|integer|min:1',
            'quantity' => 'nullable|integer|min:1',
            'expiry_date' => 'nullable|date|after:now',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'terms' => 'nullable|string',
            'redemption_instructions' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
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

        // Handle image upload if provided
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/rewards');
            $imageUrl = Storage::url($imagePath);
        }

        $reward = new Reward([
            'center_id' => $center->id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'points_cost' => $request->points_cost,
            'quantity' => $request->quantity,
            'expiry_date' => $request->expiry_date,
            'image' => $imageUrl,
            'terms' => $request->terms,
            'redemption_instructions' => $request->redemption_instructions,
            'is_active' => $request->input('is_active', true),
            'is_featured' => $request->input('is_featured', false),
        ]);

        $reward->save();

        return response()->json([
            'success' => true,
            'message' => 'Reward created successfully',
            'data' => $reward
        ], 201);
    }

    /**
     * Update an existing reward.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'sometimes|string|max:255',
            'points_cost' => 'sometimes|integer|min:1',
            'quantity' => 'nullable|integer|min:1',
            'expiry_date' => 'nullable|date|after:now',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'terms' => 'nullable|string',
            'redemption_instructions' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
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

        $reward = Reward::where('id', $id)->where('center_id', $center->id)->first();

        if (!$reward) {
            return response()->json([
                'success' => false,
                'message' => 'Reward not found or does not belong to this center'
            ], 404);
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Delete previous image if exists
            if ($reward->image) {
                $oldImagePath = str_replace('/storage', 'public', $reward->image);
                Storage::delete($oldImagePath);
            }

            $imagePath = $request->file('image')->store('public/rewards');
            $reward->image = Storage::url($imagePath);
        }

        // Update fields
        if ($request->has('title')) {
            $reward->title = $request->title;
        }
        if ($request->has('description')) {
            $reward->description = $request->description;
        }
        if ($request->has('category')) {
            $reward->category = $request->category;
        }
        if ($request->has('points_cost')) {
            $reward->points_cost = $request->points_cost;
        }
        if ($request->has('quantity')) {
            $reward->quantity = $request->quantity;
        }
        if ($request->has('expiry_date')) {
            $reward->expiry_date = $request->expiry_date;
        }
        if ($request->has('terms')) {
            $reward->terms = $request->terms;
        }
        if ($request->has('redemption_instructions')) {
            $reward->redemption_instructions = $request->redemption_instructions;
        }
        if ($request->has('is_active')) {
            $reward->is_active = $request->is_active;
        }
        if ($request->has('is_featured')) {
            $reward->is_featured = $request->is_featured;
        }

        $reward->save();

        return response()->json([
            'success' => true,
            'message' => 'Reward updated successfully',
            'data' => $reward
        ]);
    }

    /**
     * Delete a reward.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a recycling center'
            ], 403);
        }

        $reward = Reward::where('id', $id)->where('center_id', $center->id)->first();

        if (!$reward) {
            return response()->json([
                'success' => false,
                'message' => 'Reward not found or does not belong to this center'
            ], 404);
        }

        // Check if there are any pending or approved redemptions
        $hasPendingRedemptions = $reward->redemptions()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasPendingRedemptions) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete reward with pending or approved redemptions'
            ], 409);
        }

        // Delete image if exists
        if ($reward->image) {
            $imagePath = str_replace('/storage', 'public', $reward->image);
            Storage::delete($imagePath);
        }

        $reward->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reward deleted successfully'
        ]);
    }
}