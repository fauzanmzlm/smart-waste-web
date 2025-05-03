<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecyclingCenter;
use App\Models\WasteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RecyclingCenterController extends Controller
{
    public function index(Request $request)
    {
        $query = RecyclingCenter::with('wasteTypes');

        // Filter by search query
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
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

    public function show($id)
    {
        $center = RecyclingCenter::with('wasteTypes')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $center
        ]);
    }

    // For admin use to create/update recycling centers
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'hours' => 'nullable|array',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'waste_types' => 'nullable|array',
            'waste_types.*' => 'exists:waste_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/recycling_centers');
            $imageUrl = Storage::url($imagePath);
        }

        $center = RecyclingCenter::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'hours' => $request->hours,
            'description' => $request->description,
            'website' => $request->website,
            'image' => $imageUrl ?? null,
        ]);

        // Attach waste types if provided
        if ($request->has('waste_types')) {
            $center->wasteTypes()->attach($request->waste_types);
        }

        return response()->json([
            'success' => true,
            'message' => 'Recycling center created successfully',
            'data' => $center
        ], 201);
    }

    // Update recycling center
    public function update(Request $request, $id)
    {
        $center = RecyclingCenter::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'phone' => 'nullable|string|max:20',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'hours' => 'nullable|array',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'waste_types' => 'nullable|array',
            'waste_types.*' => 'exists:waste_types,id',
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
            if ($center->image) {
                $oldImagePath = str_replace('/storage', 'public', $center->image);
                Storage::delete($oldImagePath);
            }

            $imagePath = $request->file('image')->store('public/recycling_centers');
            $center->image = Storage::url($imagePath);
        }

        // Update fields
        $center->fill($request->except('image', 'waste_types'));
        $center->save();

        // Update waste types if provided
        if ($request->has('waste_types')) {
            $center->wasteTypes()->sync($request->waste_types);
        }

        return response()->json([
            'success' => true,
            'message' => 'Recycling center updated successfully',
            'data' => $center
        ]);
    }

    // Delete recycling center
    public function destroy($id)
    {
        $center = RecyclingCenter::findOrFail($id);

        // Delete image if exists
        if ($center->image) {
            $imagePath = str_replace('/storage', 'public', $center->image);
            Storage::delete($imagePath);
        }

        $center->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recycling center deleted successfully'
        ]);
    }
}
