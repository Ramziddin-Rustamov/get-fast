<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookingUpdateRequest;
use App\Http\Requests\V1\BookingStoreRequest;
use App\Http\Resources\V1\BookingResource;
use App\Services\V1\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function bookTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string|max:255',
            'passengers.*.phone' => 'required|string|max:20',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }
    
        try {
            return $this->bookingService->createBooking($request);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking creation failed: ' . $e->getMessage()
            ], 500);
        }
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
