<?php

use Illuminate\Support\Facades\Route;


Route::prefix('vehicles')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\VehicleController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'show']);
        Route::post('/', [App\Http\Controllers\Api\V1\VehicleController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'destroy']);
});

Route::prefix('trips')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\TripController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\TripController::class, 'show']);
    Route::post('/', [App\Http\Controllers\Api\V1\TripController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\Api\V1\TripController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\V1\TripController::class, 'destroy']);
});


