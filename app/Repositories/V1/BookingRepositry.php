<?php
namespace App\Repositories\V1;

use App\Models\V1\Booking;
use App\Http\Resources\V1\BookingResource;

class BookingRepository 
{

    public $errorResponse = [
        'status' => 'error',
        "message" => "Not found !"
    ];

    public $successResponse = [
        'status' => 'seccess',
        "message" => "Deleted successsfully !"
    ];
    public function getAllBookings()
    {
        return Booking::paginate(20);
    }

    public function getBookingById($id)
    {
        $booking =  Booking::find($id);
        if(is_null($booking) && empty($booking)){
            return response()->json($this->errorResponse, 404);
        }
        return response()->json(new BookingResource($booking),200);
    }

    public function createBooking(array $data)
    {
       $booking = Booking::create([
           'trip_id' => $data['trip_id'],
           'user_id' => $data['user_id'],
           'seats_booked' => $data['seats_booked'],
           'total_price' => $data['total_price'],
           'status' => $data['status'],
       ]);
       return response()->json(new BookingResource($booking));
    }

    public function updateBooking($id, array $data)
    {
        $booking = Booking::find($id);
        if(is_null($booking) && empty($booking)){
            return response()->json($this->errorResponse, 404);
        }
        $booking->update([
            'trip_id' => $data['trip_id'],
            'user_id' => $data['user_id'],
            'seats_booked' => $data['seats_booked'],
            'total_price' => $data['total_price'],
            'status' => $data['status'],
        ]);
        return response()->json(new BookingResource($booking));
    }

    public function deleteBooking($id)
    {
        $booking = Booking::find($id);
        if(is_null($booking) && empty($booking)){
            return response()->json($this->errorResponse, 404);
        }
        $booking->delete();
        return response()->json($this->successResponse,200);
    }
}
