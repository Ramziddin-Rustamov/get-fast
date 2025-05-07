<?php

namespace App\Http\Controllers;

use App\Models\V1\Trip;
use Illuminate\Http\Request;

class GeneralTripController extends Controller
{
    public function show($trip)
    {
        $trip = Trip::findOrFail($trip);
        return view('trip.show', compact('trip'));
    }
}
