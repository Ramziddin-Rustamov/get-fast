<?php

namespace App\Http\Controllers\Auth\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DriverAuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }


    public function register()
    {
        return view('auth.driver.register');
    }


    public function registerDriver(Request $request)
    {
        // 
    }

    public function loginDriver()
    {
        // 
    }

    public function verifyDriver()
    {
        // 
    }
}
