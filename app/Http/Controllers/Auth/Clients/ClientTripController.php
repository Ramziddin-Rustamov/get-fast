<?php

namespace App\Http\Controllers\Auth\Clients;

use App\Http\Controllers\Controller;
use App\Models\V1\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\V1\Trip;
use App\Models\V1\CardsUsedForOrders;

class ClientTripController extends Controller
{
    public function index()
    {
        $booking = Booking::with('trip', 'user')->where('user_id', Auth::id())->get();
        return view('auth.client.trip.index', compact('booking'));
    }


    public function bookView($trip)
    {
        $trip = Trip::with('parcels', 'driver', 'vehicle')->findOrFail($trip);
        return view('auth.client.trip.book', compact('trip'));
    }

    public function bookPost(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'user_id' => 'required|exists:users,id',
            'seats' => 'required|integer|min:1',
            'extra_phone' => 'required|string|min:9'
        ]);

        $findedTrip = Trip::findOrFail($request->trip_id);

        if ($findedTrip->available_seats < $request->seats) {
            return redirect()->back()->with('error', 'Not enough seats available for this trip!');
        }

        $findedTrip->available_seats -= $request->seats;
        $findedTrip->save();

        if ($findedTrip->available_seats == 0) {
            $findedTrip->status = 'completed';
            $findedTrip->save();
        }

        $booking = new Booking();
        $booking->trip_id = $findedTrip->id;
        $booking->user_id = Auth::id();
        $booking->seats_booked = $request->seats;
        $booking->total_price = $request->seats * $findedTrip->price_per_seat;
        $booking->status = 'pending'; // 	status	enum('pending', 'confirmed', 'cancelled')
        $booking->save();
        return redirect()->route('client.trips.index')->with('success', 'Trip booked successfully!');
    }
}
