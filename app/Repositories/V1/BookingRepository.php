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

            if ($trip->driver_id == auth()->user()->id) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'You can not book your own trip'
                    ]
                );
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

            if ($trip->status == 'cancelled') {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Trip is cancelled'
                    ]
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
            //
            $serviceFee = $totalPrice * env('SERVICE_FEE');
            $serviceFee = number_format((float)$serviceFee, 2, '.', '');

            $userBalance = UserBalance::where('user_id', auth()->user()->id)->first();
            $driverBalance = UserBalance::where('user_id', $trip->driver_id)->first();

            if (!$userBalance) {
                $userBalance = UserBalance::create([
                    'user_id' => auth()->user()->id,
                    'balance' => '00.00',
                    'tax' => env('SERVICE_FEE'), // 14%
                    'after_taxes' => '0.00'
                ]);
            }

            if (!$driverBalance) {
                $driverBalance = UserBalance::create([
                    'user_id' => $trip->driver_id,
                    'balance' => '00.00',
                    'tax' => env('SERVICE_FEE'), // 14%
                    'after_taxes' => '0.00'
                ]);
            }

            if ($userBalance->balance < $totalPrice) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Insufficient balance for booking'
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

            if ($driverBalance) {

                $reason = __('messages.driver_booking_reason', [
                    'booking_id' => $booking->id,
                    'trip_id' => $trip->id,
                    'total_price' => $totalPrice,
                    'service_fee' => $serviceFee,
                    'net_income' => $totalPrice - $serviceFee,
                ]);

                $balancetranaction = new BalanceTransaction();
                $balancetranaction->user_id = $trip->driver_id;
                $balancetranaction->type = 'credit';
                $balancetranaction->amount = $totalPrice;
                $balancetranaction->balance_before = $driverBalance->balance;
                $balancetranaction->balance_after = $driverBalance->balance + $totalPrice;
                $balancetranaction->trip_id = $trip->id;
                $balancetranaction->status = 'success';
                $balancetranaction->reason = $reason;
                $balancetranaction->reference_id = $booking->id;
                $balancetranaction->save();

                $afterTaxDriverBalance = ($driverBalance->after_taxes) + ($totalPrice - $serviceFee);
                $driverBalance->balance = ($driverBalance->balance) + ($totalPrice);
                $driverBalance->after_taxes = $afterTaxDriverBalance;
                $driverBalance->save();
            }

            if ($userBalance) {

                $reason = __('messages.user_booking_reason', [
                    'booking_id' => $booking->id,
                    'trip_id' => $trip->id,
                    'seats' => $requestedSeats,
                    'total_price' => $totalPrice,
                    'service_fee' => $serviceFee,
                ]);


                $balancetranaction = new BalanceTransaction();
                $balancetranaction->user_id = auth()->user()->id;
                $balancetranaction->type = 'debit';
                $balancetranaction->amount = $totalPrice;
                $balancetranaction->balance_before = $userBalance->balance;
                $balancetranaction->balance_after = $userBalance->balance - $totalPrice;

                $balancetranaction->trip_id = $trip->id;
                $balancetranaction->status = 'success';
                $balancetranaction->reason = $reason;
                $balancetranaction->reference_id = $booking->id;
                $balancetranaction->save();

                $userBalance->balance = ($userBalance->balance) - ($totalPrice);
                $userBalance->after_taxes = $userBalance->balance;

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
                return response()->json(['status' => 'error', 'message' => 'Booking not found']);
            }

            if (in_array($booking->status, ['cancelled', 'pending', 'completed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking cannot be cancelled at this stage.',
                ], 422);
            }

            $trip = Trip::find($booking->trip_id);
            if (is_null($trip)) {
                return response()->json(['status' => 'error', 'message' => 'Trip not found']);
            }

            $total = (float) $booking->total_price;
            $commission = round($total * env('SERVICE_FEE'), 2); // e.g. 0.1 (10%)
            $refund = round($total - $commission, 2);

            // === USER BALANCE UPDATE (refund - commission) ===
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0.00, 'currency' => 'UZS']
            );

            $userBalanceBefore = $userBalance->balance;
            $userBalance->balance += $refund;
            $userBalance->after_taxes += $refund;
            $userBalance->save();
            $userBalanceAfter = $userBalance->balance;

            $locale = app()->getLocale();

            $reason = match($locale) {
                'uz' => "Foydalanuvchi #{$booking->id} band qilgan safarni bekordi, qaytarilgan summa: {$refund} UZS, komissiya: {$commission} UZS",
                'ru' => "Пользователь отменил бронирование #{$booking->id}, возврат: {$refund} UZS, комиссия: {$commission} UZS",
                'en' => "User cancelled booking #{$booking->id}, refund: {$refund} UZS, commission: {$commission} UZS",
                default => "User cancelled booking #{$booking->id}, refund: {$refund} UZS, commission: {$commission} UZS",
            };

            BalanceTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $refund,
                'balance_before' => $userBalanceBefore,
                'balance_after' => $userBalanceAfter,
                'trip_id' => $trip->id,
                'status' => 'success',
                'reason' => $reason,
                'reference_id' => $booking->id,
                'currency' => 'UZS',
            ]);

            // === DRIVER BALANCE UPDATE (full amount returned, no commission) ===
            $driver = $trip->driver;
            $driverBalance = UserBalance::firstOrCreate(
                ['user_id' => $driver->id],
                ['balance' => 0.00, 'currency' => 'UZS']
            );

            $driverBalanceBefore = $driverBalance->balance;
            $driverBalance->balance -= $total;
            $driverBalance->after_taxes  = $driverBalance->balance;
            $driverBalance->save();
            $driverBalanceAfter = $driverBalance->balance;
            
            $locale = app()->getLocale();

            $driverReason = match($locale) {
                'uz' => "Foydalanuvchi #{$booking->id} band qilgan safarni bekordi. Haydovchiga qaytarilgan summa: {$total} UZS (komissiya olinmadi)",
                'ru' => "Пользователь отменил бронирование #{$booking->id}. Возврат водителю: {$total} UZS (комиссия не взималась)",
                'en' => "Booking #{$booking->id} cancelled by user. Driver refund: {$total} UZS (no commission taken)",
                default => "Booking #{$booking->id} cancelled by user. Driver refund: {$total} UZS (no commission taken)",
            };

            BalanceTransaction::create([
                'user_id' => $driver->id,
                'type' => 'debit',
                'amount' => $booking->total_price,
                'balance_before' => $driverBalanceBefore,
                'balance_after' => $driverBalanceAfter,
                'trip_id' => $trip->id,
                'status' => 'success',
                'reason' => $driverReason,
                'reference_id' => $booking->id,
                'currency' => 'UZS',
            ]);

            // === TRIP SEAT ADJUSTMENT ===
            $trip->available_seats += $booking->seats_booked;
            $trip->status = 'active';
            $trip->save();

            // === BOOKING STATUS UPDATE ===
            $booking->status = 'cancelled';
            $booking->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled. Refund issued, driver compensated.'
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
