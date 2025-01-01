<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\V1\TripService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\TripStoreRequest;
use App\Http\Requests\V1\TripUpdateRequest;
use App\Http\Resources\V1\TripResource;

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

    
}

