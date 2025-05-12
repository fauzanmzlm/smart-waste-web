<?php

namespace App\Http\Controllers\Api\RecyclingCenterOwner;

use App\Http\Controllers\Controller;
use App\Models\RecyclingCenter;
use App\Models\User;
use App\Models\WasteType;
use App\Models\MaterialPointConfig;
use App\Models\BonusConfig;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RecyclingCenterController extends Controller
{
    /**
     * Get all recycling centers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = RecyclingCenter::with('wasteTypes');

        // Only show active and approved centers to general users
        $query->where('is_active', true)
            ->where('status', 'approved');

        // Filter by search query
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by waste type
        if ($request->has('waste_types')) {
            $wasteTypes = explode(',', $request->waste_types);
            $query->whereHas('wasteTypes', function ($q) use ($wasteTypes) {
                $q->whereIn('name', $wasteTypes);
            });
        }

        // Sort by distance if lat/lng provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $lat = $request->latitude;
            $lng = $request->longitude;

            // We'll get all centers and sort them by distance
            $centers = $query->get();

            // Calculate distance for each center
            foreach ($centers as $center) {
                $center->distance = $center->getDistanceAttribute($lat, $lng);
            }

            // Sort by distance
            $sortedCenters = $centers->sortBy('distance');

            return response()->json([
                'success' => true,
                'data' => $sortedCenters->values()->all()
            ]);
        }

        $centers = $query->get();

        return response()->json([
            'success' => true,
            'data' => $centers
        ]);
    }

    /**
     * Get public recycling centers for non-authenticated users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function publicIndex(Request $request)
    {
        $query = RecyclingCenter::with('wasteTypes')
            ->where('is_active', true)
            ->where('status', 'approved');

        // Only return basic information for public access
        $centers = $query->select([
            'id',
            'name',
            'address',
            'latitude',
            'longitude',
            'image'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $centers
        ]);
    }

    /**
     * Get a specific recycling center by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $center = RecyclingCenter::with('wasteTypes')
            ->where('id', $id)
            ->where('is_active', true)
            ->where('status', 'approved')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $center
        ]);
    }

    /**
     * Register a new recycling center.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            // 'website' => 'nullable|url',
            'website' => 'nullable',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'hours' => 'nullable|json',
            'waste_types' => 'required|array',
            'waste_types.*' => 'exists:waste_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if user already has a recycling center
        if ($user->recyclingCenter) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a registered recycling center'
            ], 400);
        }

        // Parse hours from JSON if provided
        $hours = null;
        if ($request->has('hours')) {
            $hours = json_decode($request->hours, true);
        }

        // Handle image upload
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/recycling_centers');
            $imageUrl = Storage::url($imagePath);
        }

        DB::beginTransaction();

        try {
            // Create new recycling center
            $center = new RecyclingCenter([
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'website' => $request->website,
                'description' => $request->description,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'hours' => $hours,
                'image' => $imageUrl,
                'status' => 'pending', // New centers start as pending
                'is_active' => false,
                'user_id' => $user->id,
            ]);

            $center->save();

            // Attach waste types
            if ($request->has('waste_types')) {
                $center->wasteTypes()->attach($request->waste_types);
            }

            // Create default material point configs
            $materials = Material::all();
            foreach ($materials as $material) {
                MaterialPointConfig::create([
                    'center_id' => $center->id,
                    'material_id' => $material->id,
                    'points' => $material->default_points,
                    'is_enabled' => true,
                    'multiplier' => 1.0,
                ]);
            }

            // Create default bonus config
            // BonusConfig::create([
            //     'center_id' => $center->id,
            //     'consecutive_days_enabled' => false,
            //     'consecutive_days_bonus' => 0.5,
            //     'max_consecutive_days' => 5,
            // ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recycling center registration submitted successfully. It will be reviewed by administrators.',
                'data' => $center
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up the uploaded image if something went wrong
            if ($imageUrl) {
                $imagePath = str_replace('/storage', 'public', $imageUrl);
                Storage::delete($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to register recycling center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check the status of the user's recycling center registration.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkStatus($id)
    {
        $user = User::findOrFail($id);
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => true,
                'has_center' => false,
                'message' => 'User does not have a registered recycling center'
            ]);
        }

        $statusMessages = [
            'pending' => 'Your recycling center registration is pending approval.',
            'approved' => 'Your recycling center has been approved.',
            'rejected' => 'Your recycling center registration was rejected.',
        ];

        return response()->json([
            'success' => true,
            'has_center' => true,
            'center_status' => $center->status,
            'is_active' => $center->is_active,
            'message' => $statusMessages[$center->status] ?? 'Unknown status',
            'rejection_reason' => $center->rejection_reason
        ]);
    }

    /**
     * Get the details of the current user's recycling center.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMyCenterDetails($id)
    {
        $user = User::findOrFail($id);
        $center = $user->recyclingCenter;

        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have a registered recycling center'
            ], 404);
        }

        // Load waste types and bonus config
        $center->load('wasteTypes', 'bonusConfig', 'materialPointConfigs.material');

        return response()->json([
            'success' => true,
            'data' => $center
        ]);
    }

    /**
     * Update an existing recycling center.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'hours' => 'nullable|json',
            'waste_types' => 'sometimes|array',
            'waste_types.*' => 'exists:waste_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $center = RecyclingCenter::findOrFail($id);

        // Ensure user owns this center
        if ($center->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Ensure center is approved if trying to activate
        if ($request->has('is_active') && $request->is_active && $center->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved centers can be activated'
            ], 400);
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Delete previous image if exists
            if ($center->image) {
                $oldImagePath = str_replace('/storage', 'public', $center->image);
                Storage::delete($oldImagePath);
            }

            $imagePath = $request->file('image')->store('public/recycling_centers');
            $center->image = Storage::url($imagePath);
        }

        // Parse hours from JSON if provided
        if ($request->has('hours')) {
            $center->hours = json_decode($request->hours, true);
        }

        // Update fields that are provided
        $center->fill($request->only([
            'name',
            'address',
            'phone',
            'website',
            'description',
            'latitude',
            'longitude',
            'is_active'
        ]));

        DB::beginTransaction();

        try {
            $center->save();

            // Update waste types if provided
            if ($request->has('waste_types')) {
                $center->wasteTypes()->sync($request->waste_types);
            }

            DB::commit();

            // Reload with waste types
            $center->load('wasteTypes');

            return response()->json([
                'success' => true,
                'message' => 'Recycling center updated successfully',
                'data' => $center
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update recycling center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a recycling center.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        $user = Auth::user();
        $center = RecyclingCenter::findOrFail($id);

        // Ensure user owns this center
        if ($center->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Ensure center is approved
        if ($center->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved centers can be activated'
            ], 400);
        }

        $center->is_active = true;
        $center->save();

        return response()->json([
            'success' => true,
            'message' => 'Recycling center activated successfully',
            'data' => $center
        ]);
    }

    /**
     * Deactivate a recycling center.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deactivate($id)
    {
        $user = Auth::user();
        $center = RecyclingCenter::findOrFail($id);

        // Ensure user owns this center
        if ($center->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        $center->is_active = false;
        $center->save();

        return response()->json([
            'success' => true,
            'message' => 'Recycling center deactivated successfully',
            'data' => $center
        ]);
    }

    /**
     * Delete a recycling center (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $center = RecyclingCenter::findOrFail($id);

        // Ensure user owns this center or is admin
        if ($center->user_id !== $user->id && !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Check if there is any activity already recorded for this center
        $hasActivity = $center->rewards()->exists() ||
            $center->materialPointConfigs()->exists() ||
            $center->pointsTransactions()->exists();

        if ($hasActivity) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete center with existing activity. You can deactivate it instead.'
            ], 400);
        }

        // Soft delete the center
        $center->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recycling center deleted successfully'
        ]);
    }

    /**
     * Get all pending recycling center applications (admin only).
     *
     * @return \Illuminate\Http\Response
     */
    public function getPendingApplications()
    {
        $pendingCenters = RecyclingCenter::where('status', 'pending')
            ->with(['user:id,name,email', 'wasteTypes'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pendingCenters
        ]);
    }

    /**
     * Approve a recycling center application (admin only).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approveApplication($id)
    {
        $center = RecyclingCenter::findOrFail($id);

        if ($center->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This application has already been processed'
            ], 400);
        }

        $center->status = 'approved';
        $center->is_active = true; // Automatically activate when approved
        $center->save();

        // Notify the center owner via email or notification system
        // This would be implemented with Laravel's notification system
        // $center->user->notify(new CenterApproved($center));

        return response()->json([
            'success' => true,
            'message' => 'Recycling center application approved successfully',
            'data' => $center
        ]);
    }

    /**
     * Reject a recycling center application (admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function rejectApplication(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $center = RecyclingCenter::findOrFail($id);

        if ($center->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This application has already been processed'
            ], 400);
        }

        $center->status = 'rejected';
        $center->rejection_reason = $request->rejection_reason;
        $center->is_active = false;
        $center->save();

        // Notify the center owner via email or notification system
        // This would be implemented with Laravel's notification system
        // $center->user->notify(new CenterRejected($center));

        return response()->json([
            'success' => true,
            'message' => 'Recycling center application rejected successfully',
            'data' => $center
        ]);
    }

    /**
     * Get centers statistics (admin only).
     *
     * @return \Illuminate\Http\Response
     */
    public function getStatistics()
    {
        $stats = [
            'total_centers' => RecyclingCenter::count(),
            'active_centers' => RecyclingCenter::where('is_active', true)->count(),
            'pending_centers' => RecyclingCenter::where('status', 'pending')->count(),
            'rejected_centers' => RecyclingCenter::where('status', 'rejected')->count(),
            'approved_centers' => RecyclingCenter::where('status', 'approved')->count(),
            'centers_by_waste_type' => WasteType::withCount('recyclingCenters')->get(),
            'recent_registrations' => RecyclingCenter::with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
