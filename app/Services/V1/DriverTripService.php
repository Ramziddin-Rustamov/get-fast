<?php

namespace App\Services\V1;

use App\Repositories\V1\DriverTripRepository;

class DriverTripService
{
    protected $driverTripRepository;

    public function __construct(DriverTripRepository $driverTripRepository)
    {
        $this->driverTripRepository = $driverTripRepository;
    }

    public function getAllTrips()
    {
        return $this->driverTripRepository->getAllTrips();
    }

    public function getTripById($id)
    {
        return $this->driverTripRepository->getTripById($id);
    }

    public function createTrip(array $data)
    {
        return $this->driverTripRepository->createTrip($data);
    }

    public function updateTrip($id, array $data)
    {
        return $this->driverTripRepository->updateTrip($id, $data);
    }

    public function deleteTrip($id)
    {
        return $this->driverTripRepository->deleteTrip($id);
    }
}
