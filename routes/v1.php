<?php

use App\Http\Controllers\Api\V1\APIAuthController;
use App\Http\Controllers\Api\V1\CardController;
use Illuminate\Support\Facades\Route;


// Route::post('login', [AuthController::class, 'login']);
// Route::post('register', [AuthController::class, 'register']);
// Route::middleware('auth:driver')->group(function () {
//     Route::get('/driver-data', function () {
//         return 'Only accessible by drivers!';
//     });
// });

// Route::middleware('auth:client')->group(function () {
//     Route::get('/client-data', function () {
//         return 'Only accessible by clients!';
//     });
// });

Route::middleware('auth:api')->group(function () {
    Route::prefix('vehicles')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\VehicleController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'show']);
        Route::post('/', [App\Http\Controllers\Api\V1\VehicleController::class, 'store']);
        Route::put('/update/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\V1\VehicleController::class, 'destroy']);

        Route::get('/driver/my-vehicles', [App\Http\Controllers\Api\V1\VehicleController::class, 'getDriverVehicles']);
    });

    Route::prefix('driver/trips')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\DriverTripController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\V1\DriverTripController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\DriverTripController::class, 'show']);
        Route::delete('/cancel-trip/{id}', [App\Http\Controllers\Api\V1\DriverTripController::class, 'cancel']);
        Route::get('/get-canceled-trips/driver', [App\Http\Controllers\Api\V1\DriverTripController::class, 'getCanceledTrips']);
        Route::get('/get-active-trips/driver', [App\Http\Controllers\Api\V1\DriverTripController::class, 'getActiveTrips']);
        Route::get('/get-completed-trips/driver', [App\Http\Controllers\Api\V1\DriverTripController::class, 'getCompletedTrips']);
    });

    Route::prefix('client/trips')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\ClientTripController::class, 'index']);
        Route::get('/get-canceled-trips', [App\Http\Controllers\Api\V1\ClientTripController::class, 'canceledTrips']);
        Route::get('/get-inprogress-trips', [App\Http\Controllers\Api\V1\ClientTripController::class, 'inprogressTrips']);
        Route::get('/get-completed-trips', [App\Http\Controllers\Api\V1\ClientTripController::class, 'completedTrips']);
        Route::get('/find-trip/{id}', [App\Http\Controllers\Api\V1\ClientTripController::class, 'show']);
    });

    Route::middleware('auth:api')->prefix('public/trips')->group(function () {
        // for public view
        Route::get('/', [App\Http\Controllers\Api\V1\PublicTripController::class, 'getTripsWithLessInfo']);
        Route::get('/search/available-trips', [App\Http\Controllers\Api\V1\PublicTripController::class, 'search']);
        Route::get('/view', [App\Http\Controllers\Api\V1\PublicTripController::class, 'getAllTripsForPublic']);
        Route::get('/view/{id}', [App\Http\Controllers\Api\V1\PublicTripController::class, 'getTripByIdForPublic']);
    });

    Route::prefix('client/booking')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\BookingController::class, 'index']);
        Route::put('/update/{id}', [App\Http\Controllers\Api\V1\BookingController::class, 'update']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\BookingController::class, 'show']);
        Route::post('/', [App\Http\Controllers\Api\V1\BookingController::class, 'bookTrip']);
        Route::delete('/cancel/{id}', [App\Http\Controllers\Api\V1\BookingController::class, 'cancelBooking']);
        Route::post('/{id}/add-passenger', [App\Http\Controllers\Api\V1\BookingController::class, 'addPassengerToBooking']);
        Route::post('/{bookingId}/remove-passenger/{passengerId}', [App\Http\Controllers\Api\V1\BookingController::class, 'removePassengerFromBooking']);
    });

    Route::prefix('/user/balance-transactions')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\BalanceTransactionController::class, 'getAllUserBalanceTransactions']);
        Route::get('/pdf', [App\Http\Controllers\Api\V1\BalanceTransactionController::class, 'downloadPdfTransactions']);
        Route::get('/pdf/{id}', [App\Http\Controllers\Api\V1\BalanceTransactionController::class, 'downloadOnePdfTransaction']);
    });


    Route::prefix('driver/expired-trips')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\DriverExpiredTripsControllerApi::class, 'getExpeiredTrips']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\DriverExpiredTripsControllerApi::class, 'getExpiredTrip']);
    });

    Route::get('regions', [App\Http\Controllers\Api\V1\RegionController::class, 'index']);
    Route::get('districts', [App\Http\Controllers\Api\V1\DistrictsController::class, 'index']);
    Route::get('/districts/region/{id}', [App\Http\Controllers\Api\V1\DistrictsController::class, 'getRegion']);
    Route::get('quarters', [App\Http\Controllers\Api\V1\QuarterController::class, 'index']);
    Route::get('quarters/districts/{id}', [App\Http\Controllers\Api\V1\QuarterController::class, 'getVillagesByDistrict']);
});

Route::prefix('auth')->group(function () {

    // Ro'yxatdan o'tish va tasdiqlash
    Route::post('/register', [APIAuthController::class, 'register']);
    Route::post('/verify-code', [APIAuthController::class, 'verifyCode']);
    Route::post('/resend-code', [APIAuthController::class, 'resendCode']);
    // Login va logout
    Route::post('/login', [APIAuthController::class, 'login']);
    Route::post('/send-sms-as-test', [APIAuthController::class, 'sendSmsAsTest']);
    // Parolni unutgan holatda
    Route::post('/send-reset-code', [APIAuthController::class, 'sendResetCode']);
    Route::post('/reset-password', [APIAuthController::class, 'resetPassword']);
});

Route::prefix('auth')->middleware('auth:api')->group(function () {
    Route::post('/logout', [APIAuthController::class, 'logout']);
    Route::post('/refresh', [APIAuthController::class, 'refresh']);
    Route::post('/become-a-driver', [APIAuthController::class, 'becomeDriver']);
    Route::post('/upload-car-images', [APIAuthController::class, 'uploadVehicleImages']);
    Route::post('/upload-driver-passport-driving-licence', [APIAuthController::class, 'uploadDriverDocuments']);
    Route::post('/update-profile', [APIAuthController::class, 'updateProfile']);
    Route::get('/me', [APIAuthController::class, 'me']);
    Route::post('/update-user-language', [APIAuthController::class, 'updateUserLanguage']);
});


// Route::post('/card-list/{phoneNumber}', [CardController::class, 'cardList']);
Route::prefix('bank')->middleware('auth:api')->group(function () {
    Route::post('/card-list/{phoneNumber}', [CardController::class, 'cardList']);
    Route::get('/my-registered-cards', [CardController::class, 'myCards']);
    Route::post('/add-card', [CardController::class, 'addCard']);
    Route::post('/verify-card', [CardController::class, 'verifyCard']);
    Route::post('/get-info-about-card', [CardController::class, 'getCardInfo']);
    Route::post('/check-card-balance', [CardController::class, 'checkCardBalance']);
    Route::post('create-payment', [\App\Http\Controllers\Api\V1\PaymentController::class, 'createPayment']);
    Route::post('confirm-payment', [\App\Http\Controllers\Api\V1\PaymentController::class, 'confirmPayment']);
    Route::post('get-balance', [\App\Http\Controllers\Api\V1\PaymentController::class, 'getBalance']);
    Route::post('resend-sms', [\App\Http\Controllers\Api\V1\PaymentController::class, 'resendSms']);
    Route::post('get-payment-info', [\App\Http\Controllers\Api\V1\PaymentController::class, 'getPaymentInfo']);
    Route::get('payment-history', [\App\Http\Controllers\Api\V1\PaymentController::class, 'getPaymentHistory']);
    Route::post('refund-to-my-card-from-balance', [\App\Http\Controllers\Api\V1\PaymentController::class, 'refund']);
});
