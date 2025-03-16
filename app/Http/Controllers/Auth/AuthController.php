<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\V1\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{

    public function login()
    {
        return view('auth.login');
    }

    public function verifiyPage()
    {
        return view('auth.verify');
    }

    public function verify(Request $request)
    {
        $verificationCode = Cache::get("verify_code_{$request->user_id}");
        $userData = Cache::get("user_data_{$request->user_id}");

        $code = $request->code;
        if(is_null($code) || $code != $verificationCode){
            return back()->with('message',"Your given code is invalid ");
        }
        Auth::login($userData);
        $userData->save();
        if($userData->role == 'driver'){
            return redirect()->route('driver.auth.register.vehicle.index');
        }

        if($userData->role == 'client'){
            return redirect()->route('client.auth.register.extra-info.index');
        }
    }

    public function logout(){
        Auth::logout();
        return view('welcome');
    }

}
