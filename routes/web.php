<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\WelcomeController;


Route::get('/', [WelcomeController::class, 'index']);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('auth.login');
Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout.post')->middleware('auth');


Route::middleware(['can:admin', 'auth'])->group(function () {

    // DRIVERS
    Route::get('drivers', [DriverController::class, 'index'])->name('drivers.index');          // List all drivers
    Route::get('drivers/create', [DriverController::class, 'create'])->name('drivers.create'); // Show form to create a driver
    Route::post('drivers', [DriverController::class, 'store'])->name('drivers.store');         // Save new driver
    Route::get('drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');  // Show single driver
    Route::get('drivers/{driver}/edit', [DriverController::class, 'edit'])->name('drivers.edit'); // Edit driver
    Route::put('drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update'); // Update driver
    Route::delete('drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy'); // Delete driver
    Route::post('drivers/{driver}/send-sms', [DriverController::class, 'sendSms'])
        ->name('drivers.sendSms');
    Route::post('drivers/{driver}/transfer', [DriverController::class, 'transferBalance'])
        ->name('drivers.transfer');
    Route::post('drivers/{driver}/update-status', [DriverController::class, 'updateStatus'])
        ->name('drivers.updateStatus');
    // Driver – hamma rasmlarni o'chirish
    Route::delete('/driver/{driverId}/images', [DriverController::class, 'deleteAllDriverImages'])
        ->name('driver.images.deleteAll');

    // Vehicle – hamma rasmlarni o'chirish
    Route::delete('/vehicle/{vehicleId}/images', [DriverController::class, 'deleteAllVehicleImages'])
        ->name('vehicle.images.deleteAll');



    // CLIENTS
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // ADMINS
    Route::get('admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('admins/create', [AdminController::class, 'create'])->name('admins.create');
    Route::post('admins', [AdminController::class, 'store'])->name('admins.store');
    Route::get('admins/{admin}', [AdminController::class, 'show'])->name('admins.show');
    Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit');
    Route::put('admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
    Route::delete('admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');

    // ORDERS
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
});
