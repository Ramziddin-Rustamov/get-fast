<?php

namespace App\Services\V1;

use App\Repositories\V1\ClientTripRepository;

class ClientTripService
{
    protected $clientTripRepository;

    public function __construct(ClientTripRepository $clientTripRepository)
    {
        $this->clientTripRepository = $clientTripRepository;
    }

    public function getAllTrips()
    {
        return $this->clientTripRepository->getAllTrips();
    }

    public function getTripById($id)
    {
        return $this->clientTripRepository->getTripById($id);
    }
}
