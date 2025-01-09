<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\Polygon;
use Illuminate\Http\Request;

class MapDataController extends Controller
{
  public function index()
  {
    return view('interactive');
  }

  public function getMarkers()
  {
    return response()->json(Marker::all());
  }

  public function getPolygons()
  {
    return response()->json(Polygon::all());
  }

  public function storeMarker(Request $request)
  {
    $marker = Marker::create($request->all());
    return response()->json($marker);
  }

  public function storePolygon(Request $request)
  {
    $polygon = Polygon::create([
      'coordinates' => json_encode($request->coordinates),
    ]);
    return response()->json($polygon);
  }
}