<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\V1\TripService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\TripStoreRequest;
use App\Http\Requests\V1\TripUpdateRequest;
use App\Http\Resources\V1\TripResource;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TripController extends Controller
{
    protected $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index()
    {
        return TripResource::collection($this->tripService->getAllTrips());
    }

    public function show($id)
    {
        return $this->tripService->getTripById($id);
    }

    public function store(TripStoreRequest $request)
    {
        return $this->tripService->createTrip($request->validated());
    }

    public function update(TripUpdateRequest $request, $id)
    {
        return $this->tripService->updateTrip($id, $request->validated());
    }

    public function destroy($id)
    {
        return  $this->tripService->deleteTrip($id);
    }

    public function search(Request $request)
    {
        $from = $request->query('start_quarter_id');
        $to = $request->query('end_quarter_id');
        $departureDate = $request->query('departure_date');
        $returnDate = $request->query('return_date');
        $isRoundTrip = $request->has('is_round_trip');

        // Departure leg
        $departureTrips = Trip::where('start_quarter_id', $from)
            ->where('end_quarter_id', $to)
            ->where('status', 'active')
            ->where('start_time', '>=', Carbon::parse($departureDate)) // full datetime
            ->where('available_seats', '>', 0)
            ->get();


        $returnTrips = collect(); 

        if ($isRoundTrip && $returnDate) {
            $returnTrips = Trip::where('start_quarter_id', $to)
                ->where('end_quarter_id', $from)
                ->where('status', 'active')
                ->where('start_time', '<=', Carbon::parse($returnDate))
                ->where('available_seats', '>', 0)
                ->get();
        }

        return response()->json([
            'departure_trips' => TripResource::collection($departureTrips),
            'return_trips' => TripResource::collection($returnTrips)
        ]);
    }
}
