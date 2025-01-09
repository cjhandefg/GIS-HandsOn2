<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use Illuminate\Http\Request;

class PolygonController extends Controller
{
    /**
     * List all polygons
     */
    public function listPolygons()
    {
        $polygons = Polygon::all();
        return response()->json($polygons);
    }

    /**
     * Delete a specific polygon
     */
    public function deletePolygon($id)
    {
        try {
            $polygon = Polygon::findOrFail($id);
            $polygon->delete();
            
            return response()->json([
                'message' => 'Polygon deleted successfully',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete polygon',
                'error' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function updatePolygon(Request $request, $id)
{
    try {
        $polygon = Polygon::findOrFail($id);
        $polygon->update([
            'coordinates' => json_encode($request->coordinates)
        ]);
        
        return response()->json([
            'message' => 'Polygon updated successfully',
            'status' => 'success',
            'data' => $polygon
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update polygon',
            'error' => $e->getMessage(),
            'status' => 'error'
        ], 500);
    }
}
}