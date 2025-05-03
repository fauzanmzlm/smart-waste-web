<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        $preferences = $user->preferences;
        $badges = $user->badges;
        $totalPoints = $user->getTotalPointsAttribute();
        $itemsRecycled = $user->getItemsRecycledAttribute();

        return response()->json([
            'success' => true,
            'user' => $user,
            'preferences' => $preferences,
            'badges' => $badges,
            'stats' => [
                'totalPoints' => $totalPoints,
                'itemsRecycled' => $itemsRecycled,
                'badges' => $badges->count(),
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'location' => 'sometimes|nullable|string|max:255',
            'bio' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
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

    public function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Delete previous image if exists
        if ($user->photoURL) {
            $oldImagePath = str_replace('/storage', 'public', $user->photoURL);
            Storage::delete($oldImagePath);
        }

        // Store new image
        $path = $request->file('image')->store('public/profiles');
        $url = Storage::url($path);

        $user->photoURL = $url;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile image uploaded successfully',
            'image_url' => $url
        ]);
    }

    public function getUserAccount(Request $request)
    {
        $user = $request->user();
        $preferences = $user->preferences;
        $totalPoints = $user->getTotalPointsAttribute();
        $itemsRecycled = $user->getItemsRecycledAttribute();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'createdAt' => $user->created_at,
                'lastLogin' => $user->last_login_at,
                'accountType' => $user->account_type,
                'subscriptionStatus' => $user->subscription_status,
                'recyclingStats' => [
                    'totalItems' => $itemsRecycled,
                    'totalPoints' => $totalPoints,
                    'weeklyAverage' => $this->calculateWeeklyAverage($user->id),
                ],
                'preferences' => $preferences,
            ]
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emailNotifications' => 'sometimes|boolean',
            'pushNotifications' => 'sometimes|boolean',
            'dataUsage' => 'sometimes|boolean',
            'darkMode' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $preferences = $user->preferences;

        if (!$preferences) {
            $preferences = new UserPreference(['user_id' => $user->id]);
        }

        if ($request->has('emailNotifications')) {
            $preferences->email_notifications = $request->emailNotifications;
        }

        if ($request->has('pushNotifications')) {
            $preferences->push_notifications = $request->pushNotifications;
        }

        if ($request->has('dataUsage')) {
            $preferences->data_usage = $request->dataUsage;
        }

        if ($request->has('darkMode')) {
            $preferences->dark_mode = $request->darkMode;
        }

        $preferences->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'preferences' => $preferences
        ]);
    }

    public function deleteAccount(Request $request)
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

    private function calculateWeeklyAverage($userId)
    {
        // Logic to calculate weekly average of recycled items
        // This is a simplified version
        $user = User::find($userId);
        $totalItems = $user->recyclingHistories()->count();
        $accountAgeInWeeks = max(1, $user->created_at->diffInWeeks(now()) + 1);

        return round($totalItems / $accountAgeInWeeks, 1);
    }
}
