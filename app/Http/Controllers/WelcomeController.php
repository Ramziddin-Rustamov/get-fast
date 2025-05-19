<?php

namespace App\Http\Controllers;

use App\Models\V1\Trip;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $trips = Trip::whereIn('status', ['active', 'completed'])->get();

        return view('welcome', compact('trips'));
    }
}
