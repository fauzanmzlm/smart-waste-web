<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CleanupEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CleanupEventController extends Controller
{
    public function index(Request $request)
    {
        $query = CleanupEvent::query();

        // Filter by future events only
        if ($request->has('upcoming') && $request->upcoming) {
            $query->where('date', '>=', now()->toDateString());
        }

        // Filter by location
        if ($request->has('location')) {
            $location = $request->location;
            $query->where('location', 'like', "%{$location}%");
        }

        // Sort by date
        $query->orderBy('date', 'asc');

        $events = $query->get();

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    public function show($id)
    {
        $event = CleanupEvent::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    // Admin functions for event management
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|string',
            'location' => 'required|string|max:255',
            'organizer' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'website' => 'nullable|url',
            'contact_number' => 'nullable|string|max:20',
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
            $imagePath = $request->file('image')->store('public/cleanup_events');
            $imageUrl = Storage::url($imagePath);
        }

        $event = CleanupEvent::create([
            'title' => $request->title,
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            'organizer' => $request->organizer,
            'description' => $request->description,
            'image' => $imageUrl,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'website' => $request->website,
            'contact_number' => $request->contact_number,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cleanup event created successfully',
            'data' => $event
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $event = CleanupEvent::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'time' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'organizer' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'website' => 'nullable|url',
            'contact_number' => 'nullable|string|max:20',
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
            if ($event->image) {
                $oldImagePath = str_replace('/storage', 'public', $event->image);
                Storage::delete($oldImagePath);
            }

            $imagePath = $request->file('image')->store('public/cleanup_events');
            $request->merge(['image' => Storage::url($imagePath)]);
        }

        $event->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cleanup event updated successfully',
            'data' => $event
        ]);
    }

    public function destroy($id)
    {
        $event = CleanupEvent::findOrFail($id);

        // Delete image if exists
        if ($event->image) {
            $imagePath = str_replace('/storage', 'public', $event->image);
            Storage::delete($imagePath);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cleanup event deleted successfully'
        ]);
    }
}
