<?php

namespace App\Http\Controllers\Auth\Driver\Trip\ExpiredTrips;

use App\Models\V1\Parcel;
use Illuminate\Http\Request;
use App\Models\V1\Trip;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\V1\ExpiredTrip;
use App\Models\V1\Vehicle;
use App\Models\V1\Region;
use Illuminate\Support\Facades\DB;

class ExpiredTripsController extends Controller
{

    public function index()
    {
        $trips = ExpiredTrip::with('parcels', 'driver', 'vehicle')
        ->where('driver_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        return view('auth.driver.trip.expired.index', compact('trips'));
    }

}