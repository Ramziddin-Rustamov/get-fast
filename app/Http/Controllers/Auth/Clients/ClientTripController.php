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
        $booking = Booking::with('trip', 'client')->where('user_id', Auth::id())->get();
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
            'extra_phone' => 'required|string|min:9',
            'card_number' => 'required|string|min:16',
            'expiry_month' => 'required|integer|min:1|max:12',
            'expiry_year' => 'required|integer|min:2025|max:2035',
            'cvv' => 'required|integer|min:100|max:999',
        ]);

        $findedTrip = Trip::findOrFail($request->trip_id);

        if ($findedTrip->available_seats < $request->seats) {
            return redirect()->back()->with('error', 'Not enough seats available for this trip!');
        }

        $findedTrip->available_seats -= $request->seats;
        $findedTrip->save();

        if($findedTrip->available_seats == 0){
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

        $cardsUsedForOrders = new CardsUsedForOrders();
        $cardsUsedForOrders->user_id = Auth::id();
        $cardsUsedForOrders->order_id = $findedTrip->id;
        $cardsUsedForOrders->card_number = $request->card_number;
        $cardsUsedForOrders->expiry_month = $request->expiry_month;
        $cardsUsedForOrders->expiry_year = $request->expiry_year;
        $cardsUsedForOrders->token = $request->token;
        $cardsUsedForOrders->cvv = $request->cvv;
        $cardsUsedForOrders->save();
        return redirect()->route('client.trips.index')->with('success', 'Trip booked successfully!');
    }
}
