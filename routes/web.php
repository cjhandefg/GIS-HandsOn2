<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MapTugasController;

Route::get('/tugas1', [MapTugasController::class, 'tugas1']);

Route::get('/', function () {
  return view('welcome');
});


use App\Http\Controllers\MapDataController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\PolygonController;

Route::get('/interactive', [MapDataController::class, 'index'])->name('map.index');
Route::get('/api/markers', [MapDataController::class, 'getMarkers']);
Route::get('/api/polygons', [MapDataController::class, 'getPolygons']);
Route::post('/api/markers', [MapDataController::class, 'storeMarker']);
Route::post('/api/polygons', [MapDataController::class, 'storePolygon']);

Route::get('/api/markers/list', [MarkerController::class, 'listMarkers']);
Route::delete('/api/markers/{id}', [MarkerController::class, 'deleteMarker']);

Route::get('/api/polygons/list', [PolygonController::class, 'listPolygons']);
Route::delete('/api/polygons/{id}', [PolygonController::class, 'deletePolygon']);

Route::put('/api/markers/{id}', [MarkerController::class, 'updateMarker']);
Route::put('/api/polygons/{id}', [PolygonController::class, 'updatePolygon']);