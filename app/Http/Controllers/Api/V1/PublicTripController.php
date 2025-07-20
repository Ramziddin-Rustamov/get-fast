<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PublicTripResource;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicTripController extends Controller
{

    public $errorResponse = [
        'success' => false,
        'message' => 'Trip not found'
    ];

    public function search(Request $request)
    {
        $from = $request->query('start_quarter_id');
        $to = $request->query('end_quarter_id');
        $departureDate = $request->query('departure_date');
        $returnDate = $request->query('return_date');
        $isRoundTrip = $request->query('is_round_trip');

        // Departure leg
        $departureTrips = Trip::where('start_quarter_id', $from)
            ->where('end_quarter_id', $to)
            ->where('status', 'active')
            ->where('start_time', '>=', Carbon::parse($departureDate))
            ->where('available_seats', '>', 0)
            ->get();


        $returnTrips = collect();

        if ($isRoundTrip && $returnDate) {
            $returnTrips = Trip::where('start_quarter_id', $to)
                ->where('end_quarter_id', $from)
                ->where('status', 'active')
                ->where('start_time', '>=', Carbon::parse($returnDate))
                ->where('available_seats', '>', 0)
                ->get();
        }

        return response()->json([
            'departure_trips' => PublicTripResource::collection($departureTrips),
            'return_trips' => PublicTripResource::collection($returnTrips),
        ]);
    }

    public function getAllTripsForPublic()
    {
        $trip =  Trip::whereIn('status', ['active', 'completed', 'full'])->paginate(20);
        return response()->json(PublicTripResource::collection($trip), 200);
    }

    public function getTripByIdForPublic($id)
    {
        $trip  =  Trip::find($id);
        if (is_null($trip) && empty($trip)) {
            return response()->json($this->errorResponse, 404);
        }
        return response()->json(new PublicTripResource($trip), 200);
    }


}
