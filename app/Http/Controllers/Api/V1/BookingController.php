<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookingUpdateRequest;
use App\Http\Requests\V1\BookingStoreRequest;
use App\Http\Resources\V1\BookingResource;
use App\Services\V1\BookingService;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index()
    {
        return BookingResource::collection($this->bookingService->getAll());
    }

    public function show($id)
    {
        return $this->bookingService->getBookingById($id);
    }

    public function store(BookingStoreRequest $request)
    {
        $data = $request->validated();
        return $this->bookingService->createBooking($data);
    }

    public function update(BookingUpdateRequest $request, $id)
    {
        $data = $request->validated();
        return $this->bookingService->updateBooking($id, $data);
    }

    public function destroy($id)
    {
        return  $this->bookingService->deleteBooking($id);
    }

    
}
