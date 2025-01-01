<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PaymentController;


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
    Route::get('payment-status/{bookingId}', [App\Http\Controllers\Api\V1\PaymeeController::class,'checkPaymentStatus']);


Route::prefix('review')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\ReviewController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\ReviewController::class, 'show']);
    Route::post('/', [App\Http\Controllers\Api\V1\ReviewController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\Api\V1\ReviewController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\V1\ReviewController::class, 'destroy']);
});




