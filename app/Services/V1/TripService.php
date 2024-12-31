<?php

namespace App\Services\V1;

use App\Repositories\V1\TripRepository;

class TripService
{
    protected $tripRepository;

    public function __construct(TripRepository $tripRepository)
    {
        $this->tripRepository = $tripRepository;
    }

    public function getAllTrips()
    {
        return $this->tripRepository->getAllTrips();
    }

    public function getTripById($id)
    {
        return $this->tripRepository->getTripById($id);
    }

    public function createTrip(array $data)
    {
        return $this->tripRepository->createTrip($data);
    }

    public function updateTrip($id, array $data)
    {
        return $this->tripRepository->updateTrip($id, $data);
    }

    public function deleteTrip($id)
    {
        return $this->tripRepository->deleteTrip($id);
    }
}
