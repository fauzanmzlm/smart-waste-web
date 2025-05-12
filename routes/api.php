<?php

// use App\Http\Controllers\Api\AuthController;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// // Route::get('/user', function (Request $request) {
// //     return $request->user();
// // })->middleware('auth:sanctum');

// // Public API routes
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// // Protected API routes
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/user', [AuthController::class, 'user']);
//     Route::post('/logout', [AuthController::class, 'logout']);

//     // Add other protected API endpoints here
// });



use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RecyclingCenterOwner\RecyclingCenterController as CenterController;
use App\Http\Controllers\API\RecyclingCenterController;
use App\Http\Controllers\API\WasteTypeController;
use App\Http\Controllers\API\WasteItemController;
use App\Http\Controllers\API\RecyclingHistoryController;
use App\Http\Controllers\API\CleanupEventController;
use App\Http\Controllers\Api\PointsController;
use App\Http\Controllers\Api\RedemptionController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\API\WasteClassificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Waste classification - public
Route::post('/predict', [WasteClassificationController::class, 'predict']);

// Public resources
Route::get('/waste-types', [WasteTypeController::class, 'index']);
Route::get('/waste-types/{id}', [WasteTypeController::class, 'show']);

Route::get('/waste-items', [WasteItemController::class, 'index']);
Route::get('/waste-items/{id}', [WasteItemController::class, 'show']);

Route::get('/recycling-centers', [RecyclingCenterController::class, 'index']);
Route::get('/recycling-centers/{id}', [RecyclingCenterController::class, 'show']);

Route::get('/cleanup-events', [CleanupEventController::class, 'index']);
Route::get('/cleanup-events/{id}', [CleanupEventController::class, 'show']);

Route::get('/users/{id}', [UserController::class, 'getUserById']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // User profile
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/image', [UserController::class, 'uploadProfileImage']);
    Route::delete('/profile', [UserController::class, 'deleteAccount']);

    // User account & preferences
    Route::get('/account', [UserController::class, 'getUserAccount']);
    Route::post('/account/preferences', [UserController::class, 'updatePreferences']);

    // Recycling History
    Route::get('/recycling-history', [RecyclingHistoryController::class, 'index']);
    Route::post('/recycling-history', [RecyclingHistoryController::class, 'store']);
    Route::get('/recycling-history/{id}', [RecyclingHistoryController::class, 'show']);
    Route::delete('/recycling-history/{id}', [RecyclingHistoryController::class, 'destroy']);
    Route::get('/recycling-stats', [RecyclingHistoryController::class, 'stats']);


    // Center registration and status
    Route::post('/recycling-centers/register', [CenterController::class, 'register']);
    Route::get('/recycling-centers/my-center/{id}', [CenterController::class, 'getMyCenterDetails']);
    Route::get('/recycling-centers/status/{id}', [CenterController::class, 'checkStatus']);


    // Rewards System

    // Rewards (for users)
    Route::get('/rewards', [RewardController::class, 'index']);
    Route::get('/rewards/{id}', [RewardController::class, 'show']);
    Route::get('/rewards/featured', [RewardController::class, 'featured']);

    // Redemptions (for users)
    Route::post('/rewards/{id}/redeem', [RedemptionController::class, 'redeem']);
    Route::get('/rewards/redemptions', [RedemptionController::class, 'history']);
    Route::get('/rewards/redemptions/{id}', [RedemptionController::class, 'show']);

    // Points (for users)
    Route::get('/points/balance', [PointsController::class, 'getBalance']);
    Route::get('/points/history', [PointsController::class, 'getHistory']);
    Route::get('/points/transactions/{id}', [PointsController::class, 'getTransactionDetails']);
    Route::get('/points/summary', [PointsController::class, 'getSummary']);
    Route::get('/points/materials/rates', [PointsController::class, 'getMaterialPointsRates']);
    Route::get('/points/leaderboard', [PointsController::class, 'getLeaderboard']);

    // Center Management Routes (protected by additional middleware)
    // Route::middleware('center.owner')->group(function () {

    // Manage recycling center
    Route::post('/recycling-centers', [RecyclingCenterController::class, 'store']);
    Route::put('/recycling-centers/{id}', [RecyclingCenterController::class, 'update']);
    Route::delete('/recycling-centers/{id}', [RecyclingCenterController::class, 'destroy']);
    Route::post('/recycling-centers/{id}/activate', [RecyclingCenterController::class, 'activate']);
    Route::post('/recycling-centers/{id}/deactivate', [RecyclingCenterController::class, 'deactivate']);

    // Manage waste items and types
    Route::post('/waste-types', [WasteTypeController::class, 'store']);
    Route::put('/waste-types/{id}', [WasteTypeController::class, 'update']);
    Route::delete('/waste-types/{id}', [WasteTypeController::class, 'destroy']);
    Route::post('/waste-items', [WasteItemController::class, 'store']);
    Route::put('/waste-items/{id}', [WasteItemController::class, 'update']);
    Route::delete('/waste-items/{id}', [WasteItemController::class, 'destroy']);

    // Manage rewards
    Route::get('/center/rewards', [RewardController::class, 'centerRewards']);
    Route::post('/rewards', [RewardController::class, 'store']);
    Route::put('/rewards/{id}', [RewardController::class, 'update']);
    Route::delete('/rewards/{id}', [RewardController::class, 'destroy']);

    // Manage redemptions
    Route::get('/rewards/redemptions/pending', [RedemptionController::class, 'getPendingRedemptions']);
    Route::post('/rewards/redemptions/{id}/process', [RedemptionController::class, 'processRedemption']);
    Route::get('/rewards/statistics', [RedemptionController::class, 'getRedemptionStats']);

    // Manage points
    Route::post('/points/award', [PointsController::class, 'awardPoints']);
    Route::post('/points/recycling', [PointsController::class, 'awardRecyclingPoints']);
    Route::post('/points/materials/configure', [PointsController::class, 'configureMaterialPoints']);
    Route::get('/points/statistics', [PointsController::class, 'getPointsStatistics']);
    // });

    Route::prefix('admin')->group(function () {
        // Waste Types management
        Route::post('/waste-types', [WasteTypeController::class, 'store']);
        Route::put('/waste-types/{id}', [WasteTypeController::class, 'update']);
        Route::delete('/waste-types/{id}', [WasteTypeController::class, 'destroy']);

        // Waste Items management
        Route::post('/waste-items', [WasteItemController::class, 'store']);
        Route::put('/waste-items/{id}', [WasteItemController::class, 'update']);
        Route::delete('/waste-items/{id}', [WasteItemController::class, 'destroy']);

        // Recycling Centers management
        Route::post('/recycling-centers', [RecyclingCenterController::class, 'store']);
        Route::put('/recycling-centers/{id}', [RecyclingCenterController::class, 'update']);
        Route::delete('/recycling-centers/{id}', [RecyclingCenterController::class, 'destroy']);

        // Cleanup Events management
        Route::post('/cleanup-events', [CleanupEventController::class, 'store']);
        Route::put('/cleanup-events/{id}', [CleanupEventController::class, 'update']);
        Route::delete('/cleanup-events/{id}', [CleanupEventController::class, 'destroy']);
    });
});



// Admin routes (would be protected with admin middleware in a real app)
