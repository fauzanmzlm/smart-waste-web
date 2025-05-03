<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteItem;
use App\Models\WasteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WasteClassificationController extends Controller
{
    public function predict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Store the uploaded image temporarily
        $imagePath = $request->file('file')->store('temp');

        try {
            // In a real implementation, this would call a machine learning model
            // For now, we'll simulate a classification response

            // List of possible waste types (matches our database)
            $possibleTypes = [
                'plastic' => 0.3,
                'paper' => 0.15,
                'glass' => 0.2,
                'metal' => 0.1,
                'organic' => 0.15,
                'battery' => 0.05,
                'clothes' => 0.05,
            ];

            // Randomly select a waste type (in a real app, this would be from ML model)
            $randomIndex = array_rand($possibleTypes);
            $predictedClass = $randomIndex;
            $confidence = $possibleTypes[$randomIndex];

            // Find matching waste type in our database
            $wasteType = WasteType::where('name', 'LIKE', "%{$predictedClass}%")->first();

            // Get potential items of this type
            $potentialItems = [];
            if ($wasteType) {
                $potentialItems = WasteItem::where('waste_type_id', $wasteType->id)
                    ->select('id', 'name', 'image', 'points', 'recyclable')
                    ->get()
                    ->toArray();
            }

            // Clean up temporary image
            Storage::delete($imagePath);

            return response()->json([
                'success' => true,
                'predicted_class' => $predictedClass,
                'confidence' => $confidence,
                'waste_type' => $wasteType,
                'potential_items' => $potentialItems
            ]);
        } catch (\Exception $e) {
            // Clean up temporary image
            Storage::delete($imagePath);

            return response()->json([
                'success' => false,
                'message' => 'Classification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
