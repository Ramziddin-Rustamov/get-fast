<?php

namespace App\Repositories\V1;

use App\Models\V1\Trip;
use App\Http\Resources\V1\TripResource;
use Illuminate\Container\Attributes\Auth;

class TripRepository
{

    public $errorResponse = [
        'status' => 'error',
        "message" => "Not found !"
    ];

    public $successResponse = [
        'status' => 'seccess',
        "message" => "Deleted successsfully !"
    ];

    public function getAllTrips()
    {
        return Trip::where('status', 'active')->andWhere('driver_id', auth()->user()->id)->paginate(20);
    }

    public function getTripById($id)
    {
        $trip  =  Trip::where('driver_id', auth()->user()->id)->find($id);
        if (is_null($trip) && empty($trip)) {
            return response()->json($this->errorResponse, 404);
        }
        return response()->json(new TripResource($trip), 200);
    }

    public function createTrip(array $data)
    {
        $trip =  new Trip();
        $trip->driver_id = Auth::user()->id;
        $trip->vehicle_id = $data['vehicle_id'];
        $trip->start_location = $data['start_location'];
        $trip->end_location = $data['end_location'];
        $trip->start_time = $data['start_time'];
        $trip->end_time = $data['end_time'];
        $trip->price_per_seat = $data['price_per_seat'];
        $trip->total_seats = (int) $data['total_seats'];
        $trip->available_seats = $data['available_seats'];
        $trip->save();
        return response()->json(new TripResource($trip), 200);
    }

    public function updateTrip($id, array $data)
    {
        $trip = Trip::where('driver_id', auth()->user()->id)->find($id);
        if (is_null($trip) && empty($trip)) {
            return response()->json($this->errorResponse, 404);
        }
        $trip->update([
            'driver_id' => auth()->user()->id,
            'vehicle_id' => $data['vehicle_id'],
            'start_location' => $data['start_location'],
            'end_location' => $data['end_location'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'price_per_seat' => $data['price_per_seat'],
            'total_seats' => $data['total_seats'],
            'available_seats' => $data['available_seats'],
        ]);
        return response()->json(new TripResource($trip), 200);
    }

    public function deleteTrip($id)
    {
        $trip = Trip::where('driver_id', auth()->user()->id)->find($id);
        if (is_null($trip) && empty($trip)) {
            return response()->json($this->errorResponse, 404);
        }
        $trip->delete();
        return response()->json($this->successResponse, 200);
    }



 
}
