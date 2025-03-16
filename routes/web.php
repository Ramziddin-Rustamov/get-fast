<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\Clients\ClientAuthController;
use App\Http\Controllers\DriverPaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Auth\Driver\DriverAuthController;
use App\Http\Controllers\RegionController;
use GuzzleHttp\Client;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // for drivers 
Route::prefix('auth/driver')->group(function () { 
    // get methods   
    Route::get('register', [DriverAuthController::class,'register'])->name('driver.auth.register.index');
    Route::get('register-vehicle', [DriverAuthController::class,'vehileIndex'])->name('driver.auth.register.vehicle.index');
    Route::post('register-vehicle', [DriverAuthController::class,'createVehicle'])->name('driver.auth.register.vehicle');

     // post methods   
     Route::post('register', [DriverAuthController::class,'registerDriver'])->name('driver.auth.register.post');
     Route::post('verify', [DriverAuthController::class,'verfiyDriver'])->name('driver.auth.verify.post');
});


Route::prefix('auth/client')->group(function () { 
        // get methods   
        Route::get('register', [ClientAuthController::class,'register'])->name('client.auth.register.index');

        // post methods   
        Route::post('register', [ClientAuthController::class,'registerClient'])->name('client.auth.register.post');
        Route::post('register/extra-information', [ClientAuthController::class,'registerExtraPost'])->name('client.auth.register.extra-info.post');
        Route::get('register/extra-info', [ClientAuthController::class,'registerExtra'])->name('client.auth.register.extra-info.index');
        Route::post('login', [ClientAuthController::class,'login'])->name('client.auth.login.post');
});

Route::prefix('auth')->group(function () { 
    // get methods   
    Route::get('login', [AuthController::class,'login'])->name('auth.login.index');
    Route::get('verify', [AuthController::class,'verifiyPage'])->name('auth.verify.index');

    // post methods   
    Route::post('login', [AuthController::class,'login'])->name('auth.login.post');
    Route::post('verify', [AuthController::class,'verify'])->name('auth.verify.post');
    Route::post('logout', [AuthController::class,'logout'])->name('auth.logout.post');
});

Route::prefix('profile')->group(function () {
    Route::get('client/info', [ClientAuthController::class, 'profileInformation'])->name('profile.index.client'); 
    Route::get('driver/info', [DriverAuthController::class, 'profileInformation'])->name('profile.index.driver'); 
});


Route::middleware(['can:admin'])->group(function () {   
        Route::resource('drivers', DriverController::class);
        Route::resource('clients', ClientController::class);
        Route::resource('admins', AdminController::class);
        Route::resource('orders', OrderController::class);
        Route::post('/drivers/{driver}/reset-balance', [DriverController::class, 'resetBalance'])->name('drivers.reset-balance');
        Route::resource('driver-payments', DriverPaymentController::class)->middleware('auth');
});







