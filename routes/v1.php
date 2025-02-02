<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PaymentController;


// Route::post('login', [AuthController::class, 'login']);
// Route::post('register', [AuthController::class, 'register']);



Route::middleware('auth:driver')->group(function () {
    Route::get('/driver-data', function () {
        return 'Only accessible by drivers!';
    });
});

Route::middleware('auth:client')->group(function () {
    Route::get('/client-data', function () {
        return 'Only accessible by clients!';
    });
});

Route::prefix('vehicles')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\VehicleController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'show']);
    Route::post('/', [App\Http\Controllers\Api\V1\VehicleController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'destroy']);
});

Route::prefix('trips')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\TripController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\V1\TripController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\TripController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Api\V1\TripController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\V1\TripController::class, 'destroy']);
});

Route::prefix('booking')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\BookingController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\BookingController::class, 'show']);
    Route::post('/', [App\Http\Controllers\Api\V1\BookingController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\Api\V1\BookingController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\V1\BookingController::class, 'destroy']);
});
    Route::post('add-card', [App\Http\Controllers\Api\V1\PaymeeController::class, 'addCard']);
    Route::post('book-trip', [App\Http\Controllers\Api\V1\PaymeeController::class, 'bookTrip']);
    Route::post('process-payment', [App\Http\Controllers\Api\V1\PaymeeController::class, 'processPayment']);
    Route::post('/check-payment-status', [App\Http\Controllers\Api\V1\PaymeeController::class, 'checkPaymentStatus']);


Route::prefix('review')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\ReviewController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\ReviewController::class, 'show']);
    Route::post('/', [App\Http\Controllers\Api\V1\ReviewController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\Api\V1\ReviewController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\V1\ReviewController::class, 'destroy']);
});

Route::get('regions', [App\Http\Controllers\Api\V1\RegionController::class, 'index']);
Route::get('districts', [App\Http\Controllers\Api\V1\DistrictsController::class, 'index']);
Route::get('/districts/region/{id}', [App\Http\Controllers\Api\V1\DistrictsController::class, 'getRegion']);
Route::get('quarters', [App\Http\Controllers\Api\V1\QuarterController::class, 'index']);
Route::get('quarters/districts/{id}', [App\Http\Controllers\Api\V1\QuarterController::class, 'getVillagesByDistrict']);




