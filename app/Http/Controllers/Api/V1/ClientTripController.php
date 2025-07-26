<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\ClientTripService;

class ClientTripController extends Controller
{

    public $errorResponse = [
        'success' => false,
        'message' => 'Trip not found'
    ];

    protected $clientTripService;

    public function __construct(ClientTripService $clientTripService)
    {
        $this->clientTripService = $clientTripService;
    }

    public function index()
    {
        return $this->clientTripService->getAllTrips();
    }

    public function show($id)
    {
        return $this->clientTripService->getTripById($id);
    }

    public function completedTrips()
    {
        return $this->clientTripService->getCompletedTrips();
    }

    public function inprogressTrips()
    {
        return $this->clientTripService->getInprogressTrips();
    }

    public function canceledTrips()
    {
        return $this->clientTripService->getCanceledTrips();
    }
    
}
