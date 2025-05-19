<?php

namespace App\Http\Controllers\Auth\Clients;

use App\Http\Controllers\Controller;
use App\Models\V1\Parcel;
use App\Models\V1\ParcelBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientParcelController extends Controller
{
    public function index()
    {
        $clientBookedParcels = ParcelBooking::with(
            'client',
            'parcel.trip.driver',
            'parcel.trip.startQuarter.district.region',
            'parcel.trip.endQuarter.district.region'
        )->where('user_id', Auth::id())->get();

        return view('auth.client.parcel.index', compact('clientBookedParcels'));
    }


    public function show($parcel)
    {
        $parcel  =  Parcel::findOrFail($parcel);
        // return $parcel->trip->startQuarter->district->region->name;
        // return $parcel->trip->endQuarter->district->name;
        // return $parcel->trip->endQuarter->name;
        return view('auth.client.parcel.book', compact('parcel'));
    }

    public function sendParcel(Request $request)
    {
        $parcel = Parcel::findOrFail($request->parcel_id);

        // $request->validate([
        //     'receiver_phone' => 'required',
        //     'max_weight' => 'required|numeric|min:0.1',
        //     'price_per_kg' => 'required|numeric|min:0',
        //     'description' => 'required|string',
        //     'max_weight' => 'required|numeric|min:0.1',
        // ]);

        if ($parcel->max_weight < $request->max_weight) {
            return redirect()->back()->with('error', 'Max weight exceeded');
        }

        $parcelBooking = new ParcelBooking();
        $parcelBooking->parcel_id = $parcel->id;
        $parcelBooking->user_id = Auth::id(); // client
        $parcelBooking->receiver_phone = $request->receiver_phone;
        $parcelBooking->parcel_description = $request->description;
        $parcelBooking->weight = $request->max_weight;

        $parcel->max_weight -= $request->max_weight;
        $parcel->save();


        $parcelBooking->total_price = $request->max_weight * $parcel->price_per_kg;
        $parcelBooking->status = 'pending';
        $parcelBooking->save();

        // user card logic writen here 

        return "done";
    }
}
