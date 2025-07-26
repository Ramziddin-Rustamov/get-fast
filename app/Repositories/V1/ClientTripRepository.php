<?php

namespace App\Repositories\V1;

use App\Http\Resources\V1\ClientTripResource;
use App\Models\V1\Trip;
use Illuminate\Support\Carbon;

class ClientTripRepository
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
        $trips =  Trip::whereIn('status', ['active', 'full'])->paginate(10);
        return ClientTripResource::collection($trips);
    }

    public function getTripById($id)
    {
        $trip = Trip::where('id', $id)
            ->whereIn('status', ['active', 'full'])
            ->first();

        if (is_null($trip) && empty($trip)) {
            return response()->json($this->errorResponse, 404);
        }
        return new ClientTripResource($trip);
    }

    public function canceledTripsForClient()
    {
        // ✅ Cancelled
        $cancelledTrips = Trip::whereHas('bookings', function ($q) {
            $q->where('user_id', auth()->id())
                ->where('status', 'cancelled');
        })
            // ->orWhere(function ($q) {
            //     $q->whereIn('status', ['cancelled'])
            //         ->whereHas('bookings', function ($q2) {
            //             $q2->where('user_id', auth()->id());
            //         });
            // })
            ->get();

        if (count($cancelledTrips) == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'you have no cancelled trips'
            ], 404);
        }

        return ClientTripResource::collection($cancelledTrips);
    }

    public function getInprogressTripsForClient()
    {
        $now = Carbon::now();
        // ✅ In Progress
        $inProgressTrips = Trip::whereHas('bookings', function ($q) {
            $q->where('user_id', auth()->id())
                ->where('status', 'confirmed');
        })
            ->where('status', 'active')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->get();

        if (count($inProgressTrips) == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'you have no in progress trips'
            ], 404);
        }

        return ClientTripResource::collection($inProgressTrips);
    }


    public function getCompletedTripsForClient()
    {
        $now = Carbon::now();
        $completedTrips = Trip::whereHas('bookings', function ($q) {
            $q->where('user_id', auth()->id())
                ->where('status', 'completed');
        })
            // ->orWhere(function ($q) {
            //     $q->where('status', 'completed')
            //         ->whereHas('bookings', function ($q2) {
            //             $q2->where('user_id', auth()->id());
            //         });
            // })
            ->get();


        if (count($completedTrips) == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'you have no completed trips'
            ], 404);
        }
        return ClientTripResource::collection($completedTrips);
    }
}
