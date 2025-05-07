<?php

namespace App\Http\Controllers\Auth\Clients;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\V1\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class ClientAuthController extends Controller
{

    public function register()
    {
        return view('auth.client.register');
    }

    public function registerClient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
        ]);

        $randomCode =  99999; //rand(1000, 9999); // Tasodifiy 4 xonali kod yaratamiz

        // Foydalanuvchini yaratish va bazaga saqlash
        $user = new User();
        $user->name = $request->name;
        $user->role = 'client';
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

    public function registerExtra()
    {
        $region = Region::all();
        return view('auth.client.extra-info', [
            'regions' => $region
        ]);
    }

    public function registerExtraPost(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $user->region_id = $request->region_id;
        $user->district_id = $request->district_id;
        $user->quarter_id = $request->quarter_id;
        $user->home = $request->home;
        $user->save();
        return  view('welcome');
    }



    public function profileInformation()
    {
        $client = User::where('id', Auth::user()->id)->where('role', 'client')->first();
        return view('auth.client.profile', compact('client'));
    }

    public function profileEdit()
    {
        $client = User::where('id', Auth::user()->id)->where('role', 'client')->first();
        $regions = Region::all();
        return view('auth.client.edit', compact('client', 'regions'));
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
        $driver = User::where('id', Auth::user()->id)->where('role', 'client')->first();
        $driver->update([
            'name' => $request->name,
            'quarter_id' => $request->quarter_id,
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'home' => $request->home
        ]);
        return redirect()->route('profile.index.client')->with('success', 'Profile updated successfully');
    }
}
