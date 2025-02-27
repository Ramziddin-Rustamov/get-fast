<?php

namespace App\Http\Controllers\Auth\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ClientAuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }


    public function register()
    {
        return view('auth.client.register');
    }

    public function createClient(Request $request)
    {
        // 
    }

    public function loginClient()
    {
        // 
    }

    public function verifyClient()
    {
        // 
    }
}
