<?php

namespace App\Repositories\V1;

use App\Models\V1\Booking;
use App\Http\Resources\V1\BookingResource;
use App\Models\User;
use App\Models\V1\BookingPassengers;
use App\Models\V1\Trip;
use Illuminate\Container\Attributes\Log as AttributesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        return Booking::where('user_id', auth()->user()->id)->with('trip', 'trip.vehicle')->paginate(20);
    }

    public function getBookingById($id)
    {
        $booking = Booking::with('passengers', 'trip.vehicle', 'trip')->where('user_id', auth()->user()->id)->find($id);

        if (is_null($booking)) {
            return response()->json($this->errorResponse, 404);
        }

        return response()->json(new BookingResource($booking), 200);
    }

    public function createBooking(array $data)
    {
        try {
            $trip = Trip::with('vehicle')->find($data['trip_id']);
            if (is_null($trip)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Trip not found'
                ], 404);
            }

            $maxSeats = $trip->vehicle->seats;
            $requestedSeats = count($data['passengers']);

            if ($requestedSeats > $maxSeats) {
                return response()->json(['status' => 'error', 'message' => 'Not enough seats available'], 422);
            }

            DB::beginTransaction();

            $booking = Booking::create([
                'trip_id' => $data['trip_id'],
                'user_id' => auth()->user()->id,
                'seats_booked' => $requestedSeats,
                'total_price' => $trip->price_per_seat * $requestedSeats,
                'expired_at' => $trip->end_time
            ]);

            $trip->available_seats -= $requestedSeats;
            $trip->save();

            if ($trip->available_seats <=    0) {
                $trip->status = 'full';
                $trip->save();
            }

            foreach ($data['passengers'] as $passenger) {
                BookingPassengers::create([
                    'booking_id' => $booking->id,
                    'name' => $passenger['name'],
                    'phone' => $passenger['phone'],
                ]);
            }

            DB::commit();

            return response()->json(new BookingResource($booking), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Server error occurred please visit create booking section'], 500);
        }
    }


    public function updateBooking($id, array $data)
    {
        $booking = Booking::find($id);

        if (is_null($booking)) {
            return response()->json($this->errorResponse, 404);
        }

        try {
            DB::beginTransaction();

            // Booking ma'lumotlarini yangilash
            $booking->update([
                'trip_id' => $data['trip_id'] ?? $booking->trip_id,
                'user_id' => $data['user_id'] ?? $booking->user_id,
                'seats_booked' => isset($data['passengers']) ? count($data['passengers']) : $booking->seats_booked,
                'total_price' => isset($data['passengers'])
                    ? $booking->trip->price * count($data['passengers'])
                    : $booking->total_price,
                'status' => $data['status'] ?? $booking->status,
            ]);

            // Agar yangi yo‘lovchilar kelgan bo‘lsa — oldilarini o‘chirib, yangilarini qo‘shamiz
            if (isset($data['passengers']) && is_array($data['passengers'])) {
                // Oldingi yo‘lovchilarni o‘chirish
                BookingPassengers::where('booking_id', $booking->id)->delete();

                // Yangi yo‘lovchilarni yaratish
                foreach ($data['passengers'] as $passenger) {
                    BookingPassengers::create([
                        'booking_id' => $booking->id,
                        'name' => $passenger['name'],
                        'phone' => $passenger['phone'],
                    ]);
                }
            }

            DB::commit();

            // Yangilangan bookingni yangi ma'lumotlar bilan qaytaramiz
            $booking->load('passengers'); // Yo‘lovchilarni yangilab olish

            return response()->json(new BookingResource($booking));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Server error occurred'], 500);
        }
    }

    public function deleteBooking($id)
    {
        $booking = Booking::find($id);

        if (is_null($booking)) {
            return response()->json($this->errorResponse, 404);
        }

        try {
            $booking->delete();
            return response()->json($this->successResponse, 200);
        } catch (\Exception $e) {
            Log::error('Booking delete failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error occurred'],  500);
        }
    }
}
