<?php

namespace App\Http\Controllers\Auth\Driver\Trip;

use App\Models\V1\Parcel;
use Illuminate\Http\Request;
use App\Models\V1\Trip;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\V1\Vehicle;
use App\Models\V1\Region;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{

    public function index()
    {
        $trips = Trip::with('parcels', 'driver', 'vehicle')->where('driver_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        return view('auth.driver.trip.index', compact('trips'));
    }

    public function create()
    {
        $driverVehicles = Vehicle::where('user_id', Auth::user()->id)->get();

        return view('auth.driver.trip.create', [
            'driverVehicles' => $driverVehicles,
            'regions' => Region::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_quarter_id' => 'required|exists:quarters,id',
            'end_quarter_id' => 'required|exists:quarters,id',
            'start_time' => 'required|date',
            'end_time' => 'required|after:start_time',
            'price_per_seat' => 'required|numeric|min:0',
            'available_seats' => 'required|integer|min:1',
            'max_weight' => 'nullable|numeric|min:0.1',
            'price_per_kg' => 'nullable|numeric|min:0',
        ]);


        // Verify vehicle ownership
        $vehicle = DB::table('vehicles')
            ->where('id', $request->vehicle_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$vehicle) {
            return redirect()->back()
                ->with('error', 'You do not own this vehicle.');
        }

        // DB::beginTransaction();

        // try {
        // Create trip
        $trip = Trip::create([
            'vehicle_id' => $request->vehicle_id,
            'driver_id' => Auth::id(),
            'start_quarter_id' => $request->start_quarter_id,
            'end_quarter_id' => $request->end_quarter_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'price_per_seat' => $request->price_per_seat,
            'available_seats' => $request->available_seats, // Fixed from total_seats
            'total_seats' => $vehicle->seats
        ]);

        // Create parcel if applicable
        if ($request->filled(['max_weight', 'price_per_kg'])) {
            Parcel::create([
                'trip_id' => $trip->id,
                'max_weight' => $request->max_weight,
                'price_per_kg' => $request->price_per_kg,
            ]);
        }

        //     DB::commit();
        // } catch (\Exception $e) {
        // DB::rollBack();
        //     return redirect()->back()
        //         ->with('error', 'Failed to create trip: ' . $e->getMessage())
        //         ->withInput();
        // }

        return redirect()->route('trips.index')
            ->with('success', 'Trip created successfully!');
    }

    public function edit(Trip $trip)
    {
        return view('trip.edit', compact('trip'));
    }

    public function update(Request $request, Trip $trip)
    {
        $request->validate([
            'price_per_seat' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1',
            'available_seats' => 'required|integer|min:0',
            'max_weight' => 'nullable|numeric|min:0.1',
            'price_per_kg' => 'nullable|numeric|min:0',
        ]);

        $trip->update($request->all());

        if ($trip->parcel) {
            $trip->parcel->update([
                'max_weight' => $request->max_weight,
                'price_per_kg' => $request->price_per_kg,
            ]);
        } else if ($request->max_weight && $request->price_per_kg) {
            Parcel::create([
                'trip_id' => $trip->id,
                'max_weight' => $request->max_weight,
                'price_per_kg' => $request->price_per_kg,
            ]);
        }

        return redirect()->route('trips.index')->with('success', 'Trip updated successfully!');
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();
        return redirect()->route('trips.index')->with('success', 'Trip deleted successfully!');
    }

    
}
