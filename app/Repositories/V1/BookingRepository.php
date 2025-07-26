<?php

namespace App\Repositories\V1;

use App\Models\V1\Trip;
use App\Models\V1\Booking;
use App\Models\UserBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\V1\BookingPassengers;
use App\Http\Resources\V1\BookingResource;
use App\Models\BalanceTransaction;
use App\Models\V1\CanceledBooking;
use Illuminate\Support\Facades\Auth;

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
            if ($trip->available_seats < $requestedSeats) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Not enough seats available'
                    ],
                    422
                );
            }

            $trip->available_seats = $trip->available_seats - $requestedSeats;
            $trip->save();

            if ($trip->available_seats <= 0) {
                $trip->status = 'full';
                $trip->save();
            }

            $totalPrice = $trip->price_per_seat * $requestedSeats;
            $totalPrice = number_format((float)$totalPrice, 2, '.', '');
            $userBalance = UserBalance::where('user_id', auth()->user()->id)->first();
            if (!$userBalance) {
                $userBalance = UserBalance::create([
                    'user_id' => auth()->user()->id,
                    'balance' => '100.00'
                ]);
            }

            if ($userBalance->balance < $totalPrice) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Insufficient balance'
                    ],
                    422
                );
            }
            $booking = Booking::create([
                'trip_id' => $data['trip_id'],
                'user_id' => auth()->user()->id,
                'seats_booked' => $requestedSeats,
                'total_price' => $totalPrice,
                'status' => 'confirmed',
                'expired_at' => $trip->end_time
            ]);

            if ($userBalance) {
                $balancetranaction = new BalanceTransaction();
                $balancetranaction->user_id = auth()->user()->id;
                $balancetranaction->type = 'debit';
                $balancetranaction->amount = $totalPrice;
                $balancetranaction->balance_before = $userBalance->balance;
                $balancetranaction->balance_after = $userBalance->balance - $totalPrice;
                $balancetranaction->trip_id = $trip->id;
                $balancetranaction->status = 'success';
                $balancetranaction->reason =  'trip booking' . $booking->id . 'trip id' . $trip->id;
                $balancetranaction->reference_id = $booking->id;
                $balancetranaction->save();

                $userBalance->balance = ($userBalance->balance) - ($totalPrice);
                $userBalance->save();
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
            return response()->json(['status' => 'error', 'message' => 'Booking creation failed: ' . $e->getMessage()], 500);
        }
    }


    public function updateBooking($id, array $data)
    {
        $booking = Booking::where('user_id', auth()->user()->id)->find($id);

        if (is_null($booking)) {
            return response()->json($this->errorResponse, 404);
        }

        try {
            DB::beginTransaction();

            $requestedSeats = isset($data['passengers']) ? count($data['passengers']) : $booking->seats_booked;
            //                                         2                       1
            if ($requestedSeats > $booking->seats_booked) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'you can not add more passengers than you have already booked'
                    ],
                    422
                );
            }

            if ($requestedSeats == $booking->seats_booked) {
                // user only want to update passengers
                if (isset($data['passengers']) && is_array($data['passengers'])) {
                    BookingPassengers::where('booking_id', $booking->id)->delete();
                    foreach ($data['passengers'] as $passenger) {
                        BookingPassengers::create([
                            'booking_id' => $booking->id,
                            'name' => $passenger['name'],
                            'phone' => $passenger['phone'],
                        ]);
                    }
                }
            }


            $booking->update([
                'seats_booked' => $requestedSeats,
                'total_price' => isset($data['passengers'])
                    ? $booking->trip->price_per_seat * count($data['passengers'])
                    : $booking->total_price,
                'status' => $data['status'] ?? $booking->status,
            ]);



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

    public function cancelBooking($bookingId)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $booking = Booking::where('user_id', $user->id)->find($bookingId);
            if (is_null($booking)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'booking not found',
                ]);
            }

            if ($booking->status == 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'booking already cancelled',
                ], 422);
            }

            if ($booking->status == 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking is not confirmed yet please wait',
                ], 422);
            }

            if ($booking->status == 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking is already completed, you can not cancel it',
                ], 422);
            }

            $total = (float) $booking->total_price;

            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0.00, 'currency' => 'som'] // kerak bo‘lsa currency
            );

            // 90% refund, 10% komissiya
            $commission = round($total * 0.10, 2);
            $refund = round($total - $commission, 2);

            $balanceBefore = $userBalance->balance;
            $userBalance->balance = $userBalance->balance + $refund;
            $userBalance->save();
            $balanceAfter = $userBalance->balance;

            $trip = Trip::find($booking->trip_id);
            if(is_null($trip)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'trip not found',
                ]);
            }
            if($trip->total_seats < ($booking->seats_booked + $booking->trip->available_seats)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Available seats should not be more than total seats',
                ]);
            }
            $trip->available_seats = $trip->available_seats + $booking->seats_booked;
            $trip->status = 'active';
            $trip->save();

            // BalanceTransaction
            BalanceTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $refund,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'trip_id' => $booking->trip_id,
                'status' => 'success',
                'reason' => 'Booking canceled - 90% refund ' . $refund . ' som, ' . ' ' . $commission . ' som commission, ' . $booking->id . ' booking id, ' . $trip->id . ' trip id, ',
                'reference_id' => $booking->id,
                'currency' => 'som',
            ]);

            // Booking status update
            $booking->status = 'cancelled';
            $booking->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking canceled and refund issued.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
