<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DriverPaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RegionController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['can:admin'])->group(function () {   
        Route::resource('drivers', DriverController::class);
        Route::resource('clients', ClientController::class);
        Route::resource('admins', AdminController::class);
        Route::resource('orders', OrderController::class);
        Route::post('/drivers/{driver}/reset-balance', [DriverController::class, 'resetBalance'])->name('drivers.reset-balance');
        Route::resource('driver-payments', DriverPaymentController::class)->middleware('auth');
});







