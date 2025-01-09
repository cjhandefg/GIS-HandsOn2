<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marker;

class MarkerController extends Controller
{
    //
    public function listMarkers()
{
    $markers = Marker::all();
    return response()->json($markers);
}

public function deleteMarker($id)
{
    try {
        $marker = Marker::findOrFail($id);
        $marker->delete();
        
        return response()->json([
            'message' => 'Marker deleted successfully',
            'status' => 'success'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to delete marker',
            'error' => $e->getMessage(),
            'status' => 'error'
        ], 500);
    }
}

public function updateMarker(Request $request, $id)
{
    try {
        $marker = Marker::findOrFail($id);
        $marker->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);
        
        return response()->json([
            'message' => 'Marker updated successfully',
            'status' => 'success',
            'data' => $marker
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update marker',
            'error' => $e->getMessage(),
            'status' => 'error'
        ], 500);
    }
}

}

