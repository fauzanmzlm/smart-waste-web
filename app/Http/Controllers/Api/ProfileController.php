<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Load additional profile information
        $user->load('preferences');

        return response()->json([
            'success' => true,
            'profile' => $user
        ]);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'location' => 'sometimes|nullable|string|max:255',
            'bio' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user profile
        $user->update($request->only([
            'name',
            'email',
            'phone',
            'location',
            'bio'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Delete the authenticated user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

    /**
     * Get the authenticated user's detailed account information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAccountInfo(Request $request)
    {
        $user = $request->user();

        // Load additional account information
        $user->load('preferences', 'recyclingStats');

        // Format account data
        $accountInfo = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'bio' => $user->bio,
            'photoURL' => $user->profile_image_url,
            'createdAt' => $user->created_at,
            'lastLogin' => $user->last_login_at,
            'accountType' => $user->account_type ?? 'Standard',
            'subscriptionStatus' => $user->subscription_status ?? 'Free',
            'recyclingStats' => [
                'totalItems' => $user->recyclingStats->total_items ?? 0,
                'totalPoints' => $user->recyclingStats->total_points ?? 0,
                'weeklyAverage' => $user->recyclingStats->weekly_average ?? 0,
            ],
            'preferences' => [
                'emailNotifications' => $user->preferences->email_notifications ?? true,
                'pushNotifications' => $user->preferences->push_notifications ?? true,
                'dataUsage' => $user->preferences->data_usage ?? true,
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $accountInfo
        ]);
    }

    /**
     * Update user preferences.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePreferences(Request $request)
    {
        $user = $request->user();

        // Validate input
        $validator = Validator::make($request->all(), [
            'emailNotifications' => 'sometimes|boolean',
            'pushNotifications' => 'sometimes|boolean',
            'dataUsage' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create user preferences
        $preferences = $user->preferences ?? new UserPreference(['user_id' => $user->id]);

        // Update preferences
        if ($request->has('emailNotifications')) {
            $preferences->email_notifications = $request->emailNotifications;
        }

        if ($request->has('pushNotifications')) {
            $preferences->push_notifications = $request->pushNotifications;
        }

        if ($request->has('dataUsage')) {
            $preferences->data_usage = $request->dataUsage;
        }

        // Save preferences
        $user->preferences()->save($preferences);

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'preferences' => [
                'emailNotifications' => $preferences->email_notifications,
                'pushNotifications' => $preferences->push_notifications,
                'dataUsage' => $preferences->data_usage,
            ]
        ]);
    }

    /**
     * Upload and store user profile image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->profile_image_path) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            // Get image file
            $image = $request->file('image');

            // Create an optimized version with Intervention Image
            $img = Image::make($image->path());
            $img->fit(300, 300, function ($constraint) {
                $constraint->upsize();
            });

            // Create filename
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();

            // Save to storage
            $path = 'profiles/' . $filename;
            Storage::disk('public')->put($path, $img->encode());

            // Update user profile with image path
            $user->profile_image_path = $path;
            $user->profile_image_url = url(Storage::url($path));
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully',
                'image_url' => $user->profile_image_url
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image provided'
        ], 400);
    }
}
