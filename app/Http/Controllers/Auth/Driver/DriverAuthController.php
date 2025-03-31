<?php

namespace App\Http\Controllers\Auth\Driver;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Region;
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

    // Keshga kod va foydalanuvchini saqlash
    Cache::put("verify_code_{$user->phone}", $randomCode, now()->addMinutes(3)); // ⬅️ Kod 1 daqiqa ichida tasdiqlanishi kerak
    Cache::put("user_data_{$user->id}", $user, now()->addMinutes(3));

    return redirect()->route('auth.verify.index', [
        'user_id' => $user->id,
        'phone' => $request->phone
    ]);
  }

    public function verifyDriver()
    {
        return view('auth.verify');
    }

    public function vehicleIndex()
    {
        $region = Region::all();
        return view('auth.driver.vehicle',[
            'regions' => $region
        ]);
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
