<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WasteTypeController extends Controller
{
    public function index()
    {
        $wasteTypes = WasteType::all();

        return response()->json([
            'success' => true,
            'data' => $wasteTypes
        ]);
    }

    public function show($id)
    {
        $wasteType = WasteType::with('wasteItems')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $wasteType
        ]);
    }

    // For admin use to create/update waste types
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:waste_types',
            'icon' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $wasteType = WasteType::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Waste type created successfully',
            'data' => $wasteType
        ], 201);
    }

    // Update waste type
    public function update(Request $request, $id)
    {
        $wasteType = WasteType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:waste_types,name,' . $id,
            'icon' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:7',
            'description' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $wasteType->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Waste type updated successfully',
            'data' => $wasteType
        ]);
    }

    // Delete waste type
    public function destroy($id)
    {
        $wasteType = WasteType::findOrFail($id);
        $wasteType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Waste type deleted successfully'
        ]);
    }
}