<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TrackerLogController;
use App\Http\Controllers\Api\V1\ShiftController;

/* 
 * * In headers Accept: application/json
 */

Route::prefix('v1')->group(function () {

  Route::get('/me', function (Request $request) {
    return $request->user();
  })->middleware('auth:sanctum');

  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
  Route::post('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
  Route::post('/check-token', [AuthController::class, 'checkToken']);
  Route::get('/create-new-log', [TrackerLogController::class, 'createNewLog']);

  Route::prefix('tracker-logs')->middleware('auth:sanctum')->group(function () {
    Route::post('/start', [TrackerLogController::class, 'start']);
    Route::post('/capture', [TrackerLogController::class, 'capture']);
    Route::post('/stop', [TrackerLogController::class, 'stop']);
    Route::post('/create-initial-log', [TrackerLogController::class, 'createInitialLog']);
    Route::post('/update-log', [TrackerLogController::class, 'updateLog']);
    Route::post('/update-screenshot', [TrackerLogController::class, 'updateScreenshot']);
    Route::post('/status-update', [TrackerLogController::class, 'statusUpdateOnStart']);
  });

  Route::prefix('user')->middleware(['auth:sanctum', 'role:super-admin|admin|hr'])->group(function () {
    Route::post('/update-shift', [ShiftController::class, 'updateUserShift']);
    Route::post('/get-shift', [ShiftController::class, 'getUserShift']);
  });
  Route::post('/create-shift', [ShiftController::class, 'create'])->middleware(['auth:sanctum', 'role:super-admin|admin|hr']);
  Route::post('/total-tracked-time', [TrackerLogController::class, 'totalTrackedTime']);
  Route::post('/tracker-state', [TrackerLogController::class, 'trackerState']);
});
