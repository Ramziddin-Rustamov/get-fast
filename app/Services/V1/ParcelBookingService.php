<?php

namespace App\Services\V1;

use App\Repositories\V1\ParcelBookingRepository;

class ParcelBookingService
{
    protected $repository;

    public function __construct(ParcelBookingRepository $repository)
    {
        $this->repository = $repository;
    }

    // Client
    public function create(array $data)
    {
        return $this->repository->createBooking($data);
    }

    public function clientBookings()
    {
        return $this->repository->getClientBookings();
    }

    public function show($id)
    {
        return $this->repository->getBookingById($id);
    }

    public function updateLocation($id, array $data)
    {
        return $this->repository->updateLocation($id, $data);
    }

    public function cancel($id)
    {
        return $this->repository->cancelByClient($id);
    }

    // Driver
    public function driverBookings()
    {
        return $this->repository->getDriverBookings();
    }

    public function driverBookingsForTrip($tripId)
    {
        return $this->repository->getDriverBookingsForTrip($tripId);
    }
}
