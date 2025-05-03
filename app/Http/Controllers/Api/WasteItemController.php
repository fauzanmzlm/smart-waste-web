<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WasteItemController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteItem::with('wasteType');

        // Filter by waste type
        if ($request->has('waste_type_id')) {
            $query->where('waste_type_id', $request->waste_type_id);
        }

        // Filter by search query
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by recyclable
        if ($request->has('recyclable')) {
            $query->where('recyclable', $request->recyclable);
        }

        $wasteItems = $query->get();

        return response()->json([
            'success' => true,
            'data' => $wasteItems
        ]);
    }

    public function show($id)
    {
        $wasteItem = WasteItem::with('wasteType')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $wasteItem
        ]);
    }

    // For admin use to create/update waste items
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'waste_type_id' => 'required|exists:waste_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'recyclable' => 'boolean',
            'disposal_instructions' => 'nullable|array',
            'restrictions' => 'nullable|string',
            'alternatives' => 'nullable|string',
            'points' => 'integer|min:0',
            'ocean_impact_factors' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload if provided
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/waste_items');
            $imageUrl = Storage::url($imagePath);
        }

        $wasteItem = WasteItem::create([
            'waste_type_id' => $request->waste_type_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imageUrl,
            'recyclable' => $request->recyclable ?? true,
            'disposal_instructions' => $request->disposal_instructions,
            'restrictions' => $request->restrictions,
            'alternatives' => $request->alternatives,
            'points' => $request->points ?? 0,
            'ocean_impact_factors' => $request->ocean_impact_factors,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Waste item created successfully',
            'data' => $wasteItem
        ], 201);
    }

    // Update waste item
    public function update(Request $request, $id)
    {
        $wasteItem = WasteItem::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'waste_type_id' => 'sometimes|exists:waste_types,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'recyclable' => 'boolean',
            'disposal_instructions' => 'nullable|array',
            'restrictions' => 'nullable|string',
            'alternatives' => 'nullable|string',
            'points' => 'integer|min:0',
            'ocean_impact_factors' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Delete previous image if exists
            if ($wasteItem->image) {
                $oldImagePath = str_replace('/storage', 'public', $wasteItem->image);
                Storage::delete($oldImagePath);
            }

            $imagePath = $request->file('image')->store('public/waste_items');
            $request->merge(['image' => Storage::url($imagePath)]);
        }

        $wasteItem->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Waste item updated successfully',
            'data' => $wasteItem
        ]);
    }

    // Delete waste item
    public function destroy($id)
    {
        $wasteItem = WasteItem::findOrFail($id);

        // Delete image if exists
        if ($wasteItem->image) {
            $imagePath = str_replace('/storage', 'public', $wasteItem->image);
            Storage::delete($imagePath);
        }

        $wasteItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Waste item deleted successfully'
        ]);
    }
}
