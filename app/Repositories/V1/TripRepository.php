<?php
namespace App\Repositories\V1;

use App\Models\V1\Trip; 
use App\Http\Resources\V1\TripResource;

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
        return Trip::where('available_seats', '>', 0)->paginate(10);
    }    

/*************  ✨ Codeium Command ⭐  *************/
    /**
     * Get a trip by its id
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
/******  4b90415f-4546-443c-92f6-d3b2568121cd  *******/
    public function getTripById($id)
    {
        $trip  =  Trip::find($id);
        if(is_null($trip) && empty($trip)){
            return response()->json($this->errorResponse, 404);
        }
        return response()->json(new TripResource($trip),200);
    }

    public function createTrip(array $data)
    {
        $trip =  new Trip();
        $trip->driver_id = $data['driver_id'];
        $trip->vehicle_id = $data['vehicle_id'];
        $trip->start_location = $data['start_location'];
        $trip->end_location = $data['end_location'];
        $trip->start_time = $data['start_time'];
        $trip->end_time = $data['end_time'];
        $trip->price_per_seat = $data['price_per_seat'];
        $trip->total_seats = (int) $data['total_seats'];
        $trip->available_seats = $data['available_seats'];  
        $trip->save();
        return response()->json(new TripResource($trip),200);

    }

    public function updateTrip($id, array $data)
    {
        $trip = Trip::find($id);
        if(is_null($trip) && empty($trip)){
            return response()->json($this->errorResponse, 404);
        }
        $trip->update([
            'driver_id' => $data['driver_id'],
            'vehicle_id' => $data['vehicle_id'],
            'start_location' => $data['start_location'],
            'end_location' => $data['end_location'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'price_per_seat' => $data['price_per_seat'],
            'total_seats' => $data['total_seats'],
            'available_seats' => $data['available_seats'],
        ]);
        return response()->json(new TripResource($trip),200);
    }

    public function deleteTrip($id)
    {
        $trip = Trip::find($id);
        if(is_null($trip) && empty($trip)){
            return response()->json($this->errorResponse, 404);
        }
         $trip->delete();
        return response()->json($this->successResponse,200);
    }
}
