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
}
