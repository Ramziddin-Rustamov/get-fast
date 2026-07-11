<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ClientParcelBookingStoreRequest;
use App\Http\Requests\V1\UpdateParcelBookingLocationRequest;
use App\Services\V1\ParcelBookingService;

/**
 * Mijoz tomoni — posilka yuborish so'rovlari.
 */
class ParcelBookingController extends Controller
{
    protected $service;

    public function __construct(ParcelBookingService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->service->clientBookings();
    }

    public function store(ClientParcelBookingStoreRequest $request)
    {
        return $this->service->create($request->validated());
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function updateLocation(UpdateParcelBookingLocationRequest $request, $id)
    {
        return $this->service->updateLocation($id, $request->validated());
    }

    public function cancel($id)
    {
        return $this->service->cancel($id);
    }
}
