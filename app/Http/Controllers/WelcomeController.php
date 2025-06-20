<?php

namespace App\Http\Controllers;

use App\Models\V1\Region;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $trips = Trip::whereIn('status', ['active', 'completed'])->get();
        $regions = Region::all();
        return view('welcome', compact('trips', 'regions'));
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'start_quarter_id' => 'required|integer',
            'end_quarter_id' => 'required|integer',
            'trip_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:trip_date',
        ]);

        $from = $validated['start_quarter_id'];
        $to = $validated['end_quarter_id'];
        $departureDate = $validated['trip_date'];
        $returnDate = $request->input('return_date'); // optional
        $isRoundTrip = $request->has('return_trip'); // checkbox

        // Departure trips
        $departureTrips = Trip::where('start_quarter_id', $from)
            ->where('end_quarter_id', $to)
            ->where('status', 'active')
            ->whereDate('start_time', '>=', Carbon::parse($departureDate))
            ->where('available_seats', '>', 0)
            ->get();

        // Return trips
        $returnTrips = collect(); // bo‘sh collection default

        if ($isRoundTrip && $returnDate) {
            $returnTrips = Trip::where('start_quarter_id', $to)
                ->where('end_quarter_id', $from)
                ->where('status', 'active')
                ->whereDate('start_time', '>=', Carbon::parse($returnDate))
                ->where('available_seats', '>', 0)
                ->get();
        }

        return view('welcome', [
            'departureTrips' => $departureTrips,
            'returnTrips' => $returnTrips,
            'regions' => Region::all()
        ]);
    }
}
