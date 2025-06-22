<?php

namespace App\Repositories\V1;

use App\Http\Resources\V1\VehicleResource;
use App\Models\V1\Vehicle;
use Illuminate\Support\Facades\Auth;

class VehicleRepository
{

    public $errorResponse = [
        'status' => 'error',
        "message" => "Not found !"
    ];

    public $successResponse = [
        'status' => 'seccess',
        "message" => "Deleted successsfully !"
    ];

    public function getAll()
    {
        return VehicleResource::collection(Vehicle::where('user_id', Auth::user()->id)->get());
    }

    public function findById($id)
    {
        $vehicle = Vehicle::where('user_id', Auth::user()->id)->find($id);
        if(is_null($vehicle) && empty($vehicle)){
            return response()->json($this->errorResponse, 404);
        }
        return response()->json(new VehicleResource($vehicle),200);
    }

    public function create(array $data)
    {
        $vehicle = new Vehicle();
        $vehicle->user_id = Auth::user()->id;
        $vehicle->make = $data['make'];
        $vehicle->model = $data['model'];
        $vehicle->year = $data['year'];
        $vehicle->license_plate = $data['license_plate'];
        $vehicle->seats = $data['seats'];
        $vehicle->save();
        return response()->json(new VehicleResource($vehicle));
    }

    public function update($id, array $data)
    {
        $vehicle = Vehicle::where('user_id', Auth::user()->id)->find($id);
        if(is_null($vehicle) && empty($vehicle)){
            return response()->json($this->errorResponse, 404);
        }
        $vehicle->make = $data['make'];
        $vehicle->model = $data['model'];
        $vehicle->year = $data['year'];
        $vehicle->license_plate = $data['license_plate'];
        $vehicle->seats = $data['seats'];
        $vehicle->user_id = Auth::user()->id;
        $vehicle->save();
        return response()->json(new VehicleResource($vehicle));
    }

    public function delete($id)
    {
        $vehicle = Vehicle::where('user_id', Auth::user()->id)->find($id);
        if(is_null($vehicle) && empty($vehicle)){
            return response()->json($this->errorResponse, 404);
        }
        $vehicle->delete();
        return response()->json($this->successResponse,200);
    }

}
