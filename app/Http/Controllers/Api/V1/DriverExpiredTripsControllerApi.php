<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DriverExpiredTripsResource;
use App\Models\V1\ExpiredTrip;

class DriverExpiredTripsControllerApi extends Controller
{
    public function getExpeiredTrips()
    {
        $expiredTrips = ExpiredTrip::where('driver_id', auth()->user()->id)->paginate(20);
        if (!$expiredTrips) {
            return response()->json([
                'message' => 'No expired trips found',
                'status' => 'error'
            ], 404);
        }
        return DriverExpiredTripsResource::collection($expiredTrips);
    }

    public function getExpiredTrip($id)
    {
        $trip = ExpiredTrip::where('driver_id', auth()->user()->id)->find($id);
        if (!$trip) {
            return response()->json([
                'message' => 'No expired trip found',
                'status' => 'error'
            ], 404);
        }
        return response()->json(new DriverExpiredTripsResource($trip), 200);
    }
}
