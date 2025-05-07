<?php

namespace App\Http\Controllers\Auth\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\V1\Trip;

class ClientTripController extends Controller
{
    public function index()
    {
        $trips = []; //Trip::with('parcels', 'driver', 'vehicle')->where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        return view('auth.client.trip.index', compact('trips'));
    }
}
