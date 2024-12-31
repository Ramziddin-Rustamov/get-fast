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
        return Trip::paginate(20);
    }

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
        $trip =  Trip::create($data);
        return response()->json(new TripResource($trip),200);
    }

    public function updateTrip($id, array $data)
    {
        $trip = Trip::find($id);
        if(is_null($trip) && empty($trip)){
            return response()->json($this->errorResponse, 404);
        }

        $trip->update($data);
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
