<?php

namespace App\Http\Controllers;

use App\Models\V1\Trip;
use Illuminate\Http\Request;

class GeneralTripController extends Controller
{
    public function show($trip)
    {
        $trip = Trip::with('parcels', 'driver', 'vehicle')->findOrFail($trip);
        return view('general-trip.show', compact('trip'));
    }
}
