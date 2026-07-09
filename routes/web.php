<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SupportMessageController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\ParcelTypeController;
use App\Http\Controllers\Admin\SearchLogController;
use App\Http\Controllers\Admin\WithdrawRequestController;
use App\Http\Controllers\WelcomeController;


Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Til almashtirish (uz / ru / en) — tanlov session'da saqlanadi va barcha sahifaga ta'sir qiladi
Route::get('lang/{locale}', function (string $locale) {
    if (in_array($locale, \App\Http\Middleware\SetLocale::SUPPORTED, true)) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

// Huquqiy hujjatlar (ochiq) — Terms & Conditions va Privacy Policy (uz/ru/en)
Route::get('terms', [App\Http\Controllers\LegalController::class, 'terms'])->name('legal.terms');
Route::get('privacy', [App\Http\Controllers\LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('qoidalar', [App\Http\Controllers\LegalController::class, 'rules'])->name('legal.rules');


Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('auth.login');
Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout.post')->middleware('auth');


Route::middleware(['can:admin', 'auth'])->group(function () {
    // admin
    Route::get('/get-all/withdraw', [WithdrawRequestController::class, 'indexForAdmin'])->name('admin.withdraw.index');
    Route::post('/withdraw/{id}/approve', [WithdrawRequestController::class, 'approve'])->name('admin.withdraw.approve');
    Route::post('/withdraw/{id}/reject', [WithdrawRequestController::class, 'reject'])->name('admin.withdraw.reject');

    // Admin routes
    Route::prefix('support')->group(function () {
        Route::get('/', [SupportMessageController::class, 'index'])->name('support.index');
        Route::get('/{id}', [SupportMessageController::class, 'show'])->name('support.show');
        Route::post('/{id}/answer', [SupportMessageController::class, 'markAsAnswered'])->name('support.MarkAsAnswered');
        Route::delete('/{id}', [SupportMessageController::class, 'destroy'])->name('support.destroy');
    });


    // BROADCAST (ommaviy e'lonlar / push)
    Route::prefix('broadcasts')->group(function () {
        Route::get('/', [BroadcastController::class, 'index'])->name('broadcasts.index');
        Route::get('/create', [BroadcastController::class, 'create'])->name('broadcasts.create');
        Route::post('/', [BroadcastController::class, 'store'])->name('broadcasts.store');
        Route::get('/{id}', [BroadcastController::class, 'show'])->name('broadcasts.show');
        Route::delete('/{id}', [BroadcastController::class, 'destroy'])->name('broadcasts.destroy');
    });

    // POCHTA TURLARI (parcel types)
    Route::prefix('parcel-types')->group(function () {
        Route::get('/', [ParcelTypeController::class, 'index'])->name('parcel-types.index');
        Route::get('/create', [ParcelTypeController::class, 'create'])->name('parcel-types.create');
        Route::post('/', [ParcelTypeController::class, 'store'])->name('parcel-types.store');
        Route::get('/{parcelType}/edit', [ParcelTypeController::class, 'edit'])->name('parcel-types.edit');
        Route::put('/{parcelType}', [ParcelTypeController::class, 'update'])->name('parcel-types.update');
        Route::delete('/{parcelType}', [ParcelTypeController::class, 'destroy'])->name('parcel-types.destroy');
    });

    // QIDIRUVLAR (foydalanuvchi qidiruvlari — marketing)
    Route::get('search-logs', [SearchLogController::class, 'index'])->name('search-logs.index');

    // DRIVERS
    Route::get('drivers', [DriverController::class, 'index'])->name('drivers.index');          // List all drivers
    Route::get('drivers/create', [DriverController::class, 'create'])->name('drivers.create'); // Show form to create a driver
    Route::post('drivers', [DriverController::class, 'store'])->name('drivers.store');         // Save new driver
    Route::get('drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');  // Show single driver
    Route::get('drivers/{driver}/edit', [DriverController::class, 'edit'])->name('drivers.edit'); // Edit driver
    Route::put('drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update'); // Update driver
    Route::delete('drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy'); // Delete driver
    Route::post('drivers/{driver}/send-sms', [DriverController::class, 'sendSms'])->name('drivers.sendSms');
    Route::post('drivers/{driver}/transfer', [DriverController::class, 'refund'])->name('drivers.transfer');


    // for overall users
    Route::post('users/{userID}/withdraw', [DriverController::class, 'withdrawFromUser'])->name('users.admin.withdraw');
    Route::post('users/{userID}/pay', [DriverController::class, 'payToUserToBalance'])->name('users.admin.balance.add');

    Route::post('drivers/{driver}/update-status', [DriverController::class, 'updateStatus'])
        ->name('drivers.updateStatus');
    // Driver – hamma rasmlarni o'chirish
    Route::delete('/driver/{driverId}/images', [DriverController::class, 'deleteAllDriverImages'])
        ->name('driver.images.deleteAll');

    // Vehicle – hamma rasmlarni o'chirish
    Route::delete('/vehicle/{vehicleId}/images', [DriverController::class, 'deleteAllVehicleImages'])
        ->name('vehicle.images.deleteAll');
    Route::get('/drivers/{id}/trips', [DriverController::class, 'trips'])
        ->name('drivers.trips');
    Route::get('/drivers/{id}/documents', [DriverController::class, 'documents'])
        ->name('drivers.documents');
    Route::get('/drivers/{id}/documents/download', [DriverController::class, 'downloadDocuments'])
        ->name('drivers.documents.download');
    Route::get('/drivers/{id}/vehicles', [DriverController::class, 'vehiclesPage'])
        ->name('drivers.vehicles');
    Route::get('/vehicle/{vehicleId}/images/download', [DriverController::class, 'downloadVehicleImages'])
        ->name('vehicle.images.download');
    Route::get('/drivers/{id}/transactions', [DriverController::class, 'transactions'])
        ->name('drivers.transactions');
    Route::post('/drivers/booking/{bookingId}/passenger/{passengerId}/cancel', [DriverController::class, 'cancelPassenger'])
        ->name('drivers.passenger.cancel');
    Route::post('/drivers/trip/{tripId}/cancel', [DriverController::class, 'cancelTrip'])
        ->name('drivers.trip.cancel');
    Route::delete('/drivers/trip/{tripId}/parcel', [DriverController::class, 'disableParcel'])
        ->name('drivers.trip.parcel.disable');
    Route::post('/drivers/trip/{tripId}/parcel/enable', [DriverController::class, 'enableParcel'])
        ->name('drivers.trip.parcel.enable');
    Route::post('/drivers/parcel-booking/{parcelBookingId}/cancel', [DriverController::class, 'cancelParcelBooking'])
        ->name('drivers.parcel.cancel');

        Route::delete('drivers/{driver}/delete-driver', [DriverController::class, 'deleteDriver'])
        ->name('drivers.delete');


    // CLIENTS
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::get('clients/{client}/trips', [ClientController::class, 'trips'])->name('clients.trips');
    Route::get('clients/{client}/balance', [ClientController::class, 'balance'])->name('clients.balance');
    Route::get('clients/{client}/images', [ClientController::class, 'images'])->name('clients.images');
    Route::post('clients/{client}/send-sms', [ClientController::class, 'sendSms'])->name('clients.sendSms');
    Route::post('clients/{driver}/transfer', [ClientController::class, 'refund'])
        ->name('clients.transfer');

        Route::post('clients/{client}/update-status', [ClientController::class, 'updateStatus'])
        ->name('client.updateStatus');

        Route::get('clients/{client}/mark-as-verified', [ClientController::class, 'markAsVerified'])
        ->name('client.markAsVerified');


        Route::delete('clients/{client}/delete-user', [ClientController::class, 'deleteClient'])
        ->name('client.deleteClient');

    // ADMINS
    Route::get('admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('admins/create', [AdminController::class, 'create'])->name('admins.create');
    Route::post('admins', [AdminController::class, 'store'])->name('admins.store');
    Route::get('admins/{admin}', [AdminController::class, 'show'])->name('admins.show');
    Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit');
    Route::put('admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
    Route::delete('admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');

    // ORDERS (read-only)
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');


    Route::get('/company/dashboard', [WelcomeController::class, 'companyDashboard'])->name('company.dashboard');
    Route::get('/company/transactions', [WelcomeController::class, 'companyTransactions'])->name('company.transactions');


    Route::get('/company/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/company/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
});
