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
        return view('auth.driver.vehicle', [
            'regions' => $region
        ]);
    }

    public function createVehicle(Request $request)
    {
        $driver = User::where('id', Auth::user()->id)->where('role', 'driver')->first();

        $driver->region_id = $request->region_id;
        $driver->district_id = $request->district_id;
        $driver->quarter_id = $request->quarter_id;
        $driver->home = $request->home;
        $vehicle = new Vehicle();
        $vehicle->user_id = Auth::user()->id ?? 4;
        $vehicle->make = $request->make;
        $vehicle->model = $request->model;
        $vehicle->year = $request->year;
        $vehicle->license_plate = $request->license_plate;
        $vehicle->seats = $request->seats;
        $vehicle->save();
        $driver->save();
        return redirect()->route('home');
    }


    public function profileEdit()
    {
        $driver = User::where('id', Auth::user()->id)->where('role', 'driver')->first();
        $regions = Region::all();
        return view('auth.driver.edit', compact('driver', 'regions'));
    }

    public function updateDriver(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'quarter_id' => 'nullable|exists:quarters,id',
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'home' => 'nullable|string',
        ]);
        $driver = User::where('id', Auth::user()->id)->where('role', 'driver')->first();
        $driver->update([
            'name' => $request->name,
            'quarter_id' => $request->quarter_id,
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'home' => $request->home
        ]);
        return redirect()->route('profile.index.driver')->with('success', 'Profile updated successfully');
    }

    // public function uploadProfileImage(Request $request)
    // {
    //     $request->validate([
    //         'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:6000',
    //     ]);
    //     $driver = User::where('id', Auth::user()->id)->where('role', 'driver')->first();
    //     if($request->hasFile('profile_image')) {
    //         $file = $request->file('profile_image');
    //         $filename = time() . '.' . $file->getClientOriginalExtension();
    //         $file->move(public_path('image'), $filename);
    //         $driver->image = $filename;
    //         $driver->save();
    //         return redirect()->route('profile.index.driver')->with('success', 'Profile image updated successfully');
    //     }
    //     return redirect()->route('profile.index.driver')->with('error', 'Profile image not updated successfully');
    // }

    public function profileInformation()
    {
        $driver = User::where('id', Auth::user()->id)->where('role', 'driver')->first();
        return view('auth.driver.profile', compact('driver'));
    }


    public function addVehicleView()
    {
        return view('auth.driver.vehicle.create');
    }

    public function addVehicle(Request $request)
    {
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|string|max:255',
            'license_plate' => 'required|string|max:255|unique:vehicles,license_plate',
            'seats' => 'required|string|max:255',
        ]);

        $vehicle = Vehicle::create([
            'user_id' => Auth::user()->id,
            'make' => $request->make,
            'model' => $request->model,
            'year' => $request->year,
            'license_plate' => $request->license_plate,
            'seats' => $request->seats
        ]);

        return redirect()->route('profile.index.driver')->with('success', 'Vehicle added successfully');
    }

    public function editVehicle($id)
    {
        $vehicle = Vehicle::where('id', $id)->first();
        return view('auth.driver.vehicle.edit', compact('vehicle'));
    }

    public function updateVehicle(Request $request)
    {
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|string|max:255',
            'seats' => 'required|string|max:255',
        ]);
        $vehicle = Vehicle::where('id', $request->id)->first();
        $vehicle->update([
            'make' => $request->make,
            'model' => $request->model,
            'year' => $request->year,
            'license_plate' => $request->license_plate ?? $vehicle->license_plate,
            'seats' => $request->seats
        ]);

        return redirect()->route('profile.index.driver')->with('success', 'Vehicle updated successfully');
    }


    public function deleteVehicle($id)
    {
        $vehicle = Vehicle::where('id', $id)->first();
        if(is_null($vehicle) && empty($vehicle)){
            return redirect()->route('profile.index.driver')->with('error', 'Vehicle not found');
        }
        $vehicle->delete();
        return redirect()->route('profile.index.driver')->with('success', 'Vehicle deleted successfully');
    }

    public function getDriverVehicles()
    {
        $vehicles = Vehicle::where('user_id', Auth::user()->id)->get();
        return view('auth.driver.vehicle.index', compact('vehicles'));
    }
}
