<?php

namespace App\Http\Controllers\Auth\Driver;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class DriverAuthController extends Controller
{


    public function register()
    {
        return view('auth.driver.register');
    }

public function registerDriver(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|unique:users,phone',
    ]);

    $randomCode =  99999; //rand(1000, 9999); // Tasodifiy 4 xonali kod yaratamiz

    // Foydalanuvchini yaratish va bazaga saqlash
    $user = new User();
    $user->name = $request->name;
    $user->role = 'driver';
    $user->phone = $request->phone;
    $user->password = Hash::make($request->phone);
    $user->save();

    // Keshga kod va foydalanuvchini saqlash
    Cache::put("verify_code_{$user->id}", $randomCode, now()->addMinutes(1)); // ⬅️ Kod 1 daqiqa ichida tasdiqlanishi kerak
    Cache::put("user_data_{$user->id}", $user, now()->addMinutes(1));

    // ⬇️ 1 daqiqa ichida tasdiqlanmasa, foydalanuvchini bazadan o‘chirish
    dispatch(function () use ($user) {
        if (!Cache::has("verify_code_{$user->id}")) {
            $user->delete();
        }
    })->delay(now()->addMinutes(1));

    return view('auth.verify', [
        'user_id' => $user->id
    ]);
}

    public function loginDriver()
    {
        // 
    }

    public function verifyDriver()
    {
        return view('auth.verify');
    }

    public function vehileIndex()
    {
        return view('auth.driver.vehicle');
    }

    public function createVehicle(Request $request)
    {
        $vehicle = new Vehicle();
        $vehicle->user_id = Auth::user()->id ?? 4; 
        $vehicle->make = $request->make;
        $vehicle->model = $request->model;
        $vehicle->year = $request->year;
        $vehicle->license_plate = $request->license_plate;
        $vehicle->seats = $request->seats;
        $vehicle->save();
        return redirect()->route('home');
    }

    public function profileInformation()
    {
        $driver = User::where('id', Auth::user()->id)->where('role', 'driver')->first();
        return view('auth.driver.profile',compact('driver'));
    }

  
}
