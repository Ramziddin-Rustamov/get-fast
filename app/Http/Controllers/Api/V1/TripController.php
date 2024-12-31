<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\V1\TripService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class TripController extends Controller
{
    protected $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index()
    {
        return response()->json($this->tripService->getAllTrips());
    }

    public function show($id)
    {
        return response()->json($this->tripService->getTripById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_location' => 'required|string',
            'end_location' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'price_per_seat' => 'required|numeric',
            'total_seats' => 'required|integer',
            'available_seats' => 'required|integer|max:total_seats',
        ]);

        return response()->json($this->tripService->createTrip($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'driver_id' => 'sometimes|exists:users,id',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'start_location' => 'sometimes|string',
            'end_location' => 'sometimes|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'nullable|date',
            'price_per_seat' => 'sometimes|numeric',
            'total_seats' => 'sometimes|integer',
            'available_seats' => 'sometimes|integer|max:total_seats',
        ]);

        return response()->json($this->tripService->updateTrip($id, $data));
    }

    public function destroy($id)
    {
        $this->tripService->deleteTrip($id);
        return response()->json(['message' => 'Trip deleted successfully']);
    }
}

