<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\V1\Vehicle;

class AuthController extends Controller
{

    public function login()
    {
        return view('auth.login');
    }

    public function loginUser(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $randomCode = 99999;
        $user = User::where('phone', $request->phone)->first();

        Cache::put("verify_code_{$request->phone}", $randomCode, now()->addMinutes(3));
        Cache::put("user_data_{$user->id}", $user, now()->addMinutes(3));

        return redirect()->route('auth.verify.index', [
            'user_id' => $user->id,
            'phone' => $request->phone
        ]);
    }

    public function verifiyPage(Request $request)
    {
        return view('auth.verify', [
            'user_id' => $request->user_id,
            'phone' => $request->phone,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $verificationCode = Cache::get("verify_code_{$request->phone}");
        $userData = Cache::get("user_data_{$request->user_id}");
        if (is_null($verificationCode) || $request->code != $verificationCode) {
            return back()->with('message', "Your given code is invalid.");
        }
        $userData->save();
        Auth::login($userData);

        Cache::forget("verify_code_{$request->phone}");
        Cache::forget("user_data_{$request->user_id}");


        if ($userData->role == 'driver') {
            $vehicleExists = Vehicle::where('user_id', $userData->id)->get()->count();
            if (!$vehicleExists) {
                return redirect()->route('driver.auth.register.vehicle.index');
            }
            return redirect()->route('home');
        }

        if ($userData->role == 'client') {
            if ($userData->region_id == null || $userData->district_id == null || $userData->quarter_id == null || $userData->home == null) {
                return redirect()->route('client.auth.register.extra-info.index');
            }
            return redirect()->route('home');
        }

        if ($userData->role == 'admin') {
            return redirect()->route('admins.index');
        }

        return back()->with('message', "User role not found.");
    }

    public function logout()
    {
        Auth::logout();
        return view('welcome');
    }
}
