<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookingUpdateRequest;
use App\Http\Requests\V1\BookingStoreRequest;
use App\Http\Resources\V1\BookingResource;
use App\Services\V1\BookingService;
use Illuminate\Http\Request;

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

    public function bookTrip(BookingStoreRequest $request)
    {
        $data = $request->validated();
        return $this->bookingService->createBooking($data);
    }

    public function update(Request $request, $id)
    {
        return $this->bookingService->updateBooking($id, $request->all());
    }

    public function cancelBooking($id)
    {
        
        return $this->bookingService->cancelBooking($id);
    }
}
