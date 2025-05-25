<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassificationHistory;
use App\Models\WasteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClassificationHistoryController extends Controller
{
    /**
     * Get classification history for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getUserHistory(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user();

            // Get query parameters for filtering
            $wasteType = WasteType::where('name', $request->input('waste_type'))->first();

            // Base query for classification history
            $query = ClassificationHistory::with(['wasteType'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Apply waste type filter if provided
            if ($wasteType) {
                $query->where('waste_type_id', $wasteType->id);
            }

            // Paginate results
            // $history = $query->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $query->get(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve classification history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10048', // Validate the image field
            'waste_type_id' => 'required|integer', // Validate the waste type ID
            'confidence_score' => 'required', // Validate the confidence score
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422); // 422 for validation errors
        }

        try {
            // Store the uploaded image
            $image = $request->file('image');
            $filePath = $image->storeAs('classifications', $image->getClientOriginalName());

            // Save classification history
            $classificationHistory = new ClassificationHistory([
                'user_id' => Auth::user()->id,
                'waste_type_id' => $request->waste_type_id,
                'confidence_score' => $request->confidence_score,
                'image' => $filePath,
            ]);
            $classificationHistory->save();

            return response()->json([
                'success' => true,
                'message' => 'Classification history saved successfully',
            ]);
        } catch (\Exception $e) {
            // In case of an error, delete the image from storage
            // Storage::delete($imagePath);

            return response()->json([
                'success' => false,
                'message' => 'Classification failed',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error for general failure
        }
    }


    /**
     * Get classification history for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $history = ClassificationHistory::with(['wasteType', 'wasteItem'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}
