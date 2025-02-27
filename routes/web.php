<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\Clients\ClientAuthController;
use App\Http\Controllers\DriverPaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Auth\Driver\DriverAuthController;
use App\Http\Controllers\RegionController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // for drivers 
Route::prefix('auth/driver')->group(function () { 
    // get methods   
    Route::get('register', [DriverAuthController::class,'register'])->name('driver.auth.register.index');
    Route::get('login', [DriverAuthController::class,'login'])->name('driver.auth.login.index');
    Route::get('verify', [DriverAuthController::class,'verfiy'])->name('driver.auth.verify.index');

     // post methods   
     Route::post('register', [DriverAuthController::class,'registerDriver'])->name('driver.auth.register.post');
     Route::post('login', [DriverAuthController::class,'loginDriver'])->name('driver.auth.login.post');
     Route::post('verify', [DriverAuthController::class,'verfiyDriver'])->name('driver.auth.verify.post');
});


Route::prefix('auth/client')->group(function () { 
        // get methods   
        Route::get('register', [ClientAuthController::class,'register'])->name('client.auth.register.index');
        Route::get('login', [ClientAuthController::class,'login'])->name('client.auth.login.index');
        Route::get('verify', [ClientAuthController::class,'verfiy'])->name('client.auth.verify.index');

        // post methods   
        Route::post('register', [ClientAuthController::class,'register'])->name('client.auth.register.post');
        Route::post('login', [ClientAuthController::class,'login'])->name('client.auth.login.post');
        Route::post('verify', [ClientAuthController::class,'verfiy'])->name('client.auth.verify.post');
});

Route::middleware(['can:admin'])->group(function () {   
        Route::resource('drivers', DriverController::class);
        Route::resource('clients', ClientController::class);
        Route::resource('admins', AdminController::class);
        Route::resource('orders', OrderController::class);
        Route::post('/drivers/{driver}/reset-balance', [DriverController::class, 'resetBalance'])->name('drivers.reset-balance');
        Route::resource('driver-payments', DriverPaymentController::class)->middleware('auth');
});







