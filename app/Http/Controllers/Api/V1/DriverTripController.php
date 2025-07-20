<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\V1\DriverTripService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DriverTripStoreRequest;
use App\Http\Requests\V1\DriverTripUpdate;

class DriverTripController extends Controller
{

    public $errorResponse = [
        'success' => false,
        'message' => 'Trip not found'
    ];

    protected $driverTripService;

    public function __construct(DriverTripService $driverTripService)
    {
        $this->driverTripService = $driverTripService;
    }

    public function index()
    {
        return $this->driverTripService->getAllTrips();
    }

    public function show($id)
    {
        return $this->driverTripService->getTripById($id);
    }

    public function store(DriverTripStoreRequest $request)
    {
        return $this->driverTripService->createTrip($request->validated());
    }

    public function update(DriverTripUpdate $request, $id)
    {
        return $this->driverTripService->updateTrip($id, $request->validated());
    }

    public function destroy($id)
    {
        return  $this->driverTripService->deleteTrip($id);
    }
}
