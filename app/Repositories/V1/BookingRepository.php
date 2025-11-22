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
use Illuminate\Support\Facades\Auth;
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;

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
                        'message' => 'Trip already is cancelled'
                    ]
                );
            }



            $userBalance = UserBalance::where('user_id', auth()->user()->id)->first();
            $driverBalance = UserBalance::where('user_id', $trip->driver_id)->first();
            $companyBalance = CompanyBalance::first();

            if (!$companyBalance) {
                $companyBalance = CompanyBalance::create([
                    'balance' => 0,
                    'total_income' => 0,
                ]);
            }

            // Trip available seats update
            $trip->available_seats -= $requestedSeats;
            if ($trip->available_seats <= 0) {
                $trip->status = 'full';
            }
            $trip->save();

            if (!$userBalance) {
                $userBalance = UserBalance::create([
                    'user_id' => auth()->user()->id,
                    'balance' => '00.00',
                    'tax' => '', // 14%
                    'after_taxes' => '0.00'
                ]);
            }

            if (!$driverBalance) {
                $driverBalance = UserBalance::create([
                    'user_id' => $trip->driver_id,
                    'balance' => '00.00',
                    'tax' => '',
                    'after_taxes' => '0.00'
                ]);
            }



            $totalPrice = $trip->price_per_seat * $requestedSeats;
            $totalPrice = number_format((float)$totalPrice, 2, '.', '');
            //


            if ($userBalance->balance < $totalPrice) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Insufficient balance for booking'
                    ],
                    422
                );
            }


            $serviceFeePercent = env('SERVICE_FEE_FOR_COMPLITING_ORDER', 0); // 5
            $serviceFee = $totalPrice * ($serviceFeePercent / 100); // foizga o‘tkazish
            $serviceFee = number_format((float)$serviceFee, 2, '.', '');

            $net_income = $totalPrice - $serviceFee; // for driver
            $net_income = number_format((float)$net_income, 2, '.', '');



            $booking = Booking::create([
                'trip_id' => $data['trip_id'],
                'user_id' => auth()->user()->id,
                'seats_booked' => $requestedSeats,
                'total_price' => $totalPrice,
                'status' => 'confirmed',
                'expired_at' => $trip->end_time
            ]);

            if ($driverBalance) {

                $reason = [
                    'uz' => "You received a new booking (Booking ID: $booking->id) for your trip (Trip ID: $trip->id). Total received: $net_income UZS. Service fee: $serviceFee UZS. Overall earnings: $totalPrice UZS.",
                    'en' => "You received a new booking (Booking ID: $booking->id) for your trip (Trip ID: $trip->id). Total received: $net_income UZS. Service fee: $serviceFee UZS. Overall earnings: $totalPrice UZS.",
                    'ru' => "You received a new booking (Booking ID: $booking->id) for your trip (Trip ID: $trip->id). Total received: $net_income UZS. Service fee: $serviceFee UZS. Overall earnings: $totalPrice UZS."
                ];

                $driverBalanceTransaction = new BalanceTransaction();
                $driverBalanceTransaction->user_id = $trip->driver_id;
                $driverBalanceTransaction->type = 'credit';
                $driverBalanceTransaction->amount = $net_income;
                $driverBalanceTransaction->balance_before = $driverBalance->balance;
                $driverBalanceTransaction->balance_after = $driverBalance->balance + $net_income;
                $driverBalanceTransaction->trip_id = $trip->id;
                $driverBalanceTransaction->status = 'success';
                $driverBalanceTransaction->reason = $reason['uz'];
                $driverBalanceTransaction->reference_id = $booking->id;
                $driverBalanceTransaction->save();

                $driverBalance->balance = ($driverBalance->balance) + ($net_income);
                $driverBalance->save();
            }

            if ($userBalance) {

                $reasonForClient = [
                    'uz' => "You made a new booking (Booking ID: $booking->id) for your trip (Trip ID: $trip->id). Total price: $totalPrice UZS",
                    'en' => "You made a new booking (Booking ID: $booking->id) for your trip (Trip ID: $trip->id). Total price: $totalPrice UZS",
                    'ru' => "You made a new booking (Booking ID: $booking->id) for your trip (Trip ID: $trip->id). Total price: $totalPrice UZS"
                ];


                $clientBalanceTranaction = new BalanceTransaction();
                $clientBalanceTranaction->user_id = auth()->user()->id;
                $clientBalanceTranaction->type = 'debit';
                $clientBalanceTranaction->amount = $totalPrice;
                $clientBalanceTranaction->balance_before = $userBalance->balance;
                $clientBalanceTranaction->balance_after = $userBalance->balance - $totalPrice;

                $clientBalanceTranaction->trip_id = $trip->id;
                $clientBalanceTranaction->status = 'success';
                $clientBalanceTranaction->reason = $reasonForClient['uz'];
                $clientBalanceTranaction->reference_id = $booking->id;
                $clientBalanceTranaction->save();

                $userBalance->balance = ($userBalance->balance) - ($totalPrice);
                $userBalance->save();
            }

            if ($companyBalance) {

                $reasonCompany = [
                    'uz' => "Client made a new booking (Booking ID: $booking->id) for driver trip (Trip ID: $trip->id). Total price: $totalPrice UZS Service fee: $serviceFee UZS",
                    'en' => "Client made a new booking (Booking ID: $booking->id) for driver trip (Trip ID: $trip->id). Total price: $totalPrice UZS Service fee: $serviceFee UZS",
                    'ru' => "Client made a new booking (Booking ID: $booking->id) for driver trip (Trip ID: $trip->id). Total price: $totalPrice UZS Service fee: $serviceFee UZS"
                ];

                $companyBalanceTransaction = new CompanyBalanceTransaction();
                $companyBalanceTransaction->company_balance_id = $companyBalance->id;
                $companyBalanceTransaction->amount =  $serviceFee;
                $companyBalanceTransaction->balance_before = $companyBalance->balance;
                $companyBalanceTransaction->balance_after = $companyBalance->balance + $serviceFee;
                $companyBalanceTransaction->trip_id = $trip->id;
                $companyBalanceTransaction->booking_id = $booking->id;
                $companyBalanceTransaction->reason = $reasonCompany['uz'];
                $companyBalanceTransaction->save();

                $companyBalance->balance = ($companyBalance->balance) + ($serviceFee);
                $companyBalance->total_income = ($companyBalance->total_income) + ($serviceFee);
                $companyBalance->save();
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
            if (!$booking) {
                return response()->json(['status' => 'error', 'message' => 'Booking not found']);
            }

            if (in_array($booking->status, ['cancelled', 'pending', 'completed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking cannot be cancelled at this stage. cancelled, pending, completed',
                ], 422);
            }

            $trip = Trip::find($booking->trip_id);
            if (!$trip) {
                return response()->json(['status' => 'error', 'message' => 'Trip not found']);
            }

            // ❗ Booking cancel 2 soatdan oldin bo'lishi kerak
            $tripStart = \Carbon\Carbon::parse($trip->start_time);
            $now = \Carbon\Carbon::now();
            if ($now->greaterThanOrEqualTo($tripStart->subHours(2))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Safar boshlanishiga 2 soatdan kam vaqt qolgani uchun bekor qilish mumkin emas.'
                ], 422);
            }

            $total = (float) $booking->total_price;

            // Cancelation fee foydalanuvchidan ushlab qolish
            $cancelationFee = round($total * env('SERVICE_FEE_FOR_CANCELATION') / 100, 2); // 100000 * 0.5 = 50000
            $refundForClient = round($total - $cancelationFee, 2);

            // === USER BALANCE UPDATE ===
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0.00, 'currency' => 'UZS']
            );
            $userBalance->balance = $userBalance->balance + $refundForClient;
            $userBalance->save();


            $reasonForClientCancelation = [
                'uz' => "Foydalanuvchi #{$booking->id} band qilgan safarni bekordi, qaytarilgan summa: {$refundForClient} UZS, bekor qilish komissiyasi: {$cancelationFee} UZS",
                'ru' => "Пользователь отменил бронирование #{$booking->id}, возврат: {$refundForClient} UZS, комиссия за отмену: {$cancelationFee} UZS",
                'en' => "User cancelled booking #{$booking->id}, refund: {$refundForClient} UZS, cancellation fee: {$cancelationFee} UZS",
            ];

            BalanceTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $refundForClient,
                'balance_before' => $userBalance->balance,
                'balance_after' => $userBalance->balance + $refundForClient,
                'trip_id' => $trip->id,
                'status' => 'success',
                'reason' => $reasonForClientCancelation['uz'],
                'reference_id' => $booking->id,
                'currency' => 'UZS',
            ]);

            // === DRIVER BALANCE UPDATE (SERVICE_FEE_FOR_DRIVERS 5%) ===
            $driver = $trip->driver;
            $driverBalance = UserBalance::firstOrCreate(
                ['user_id' => $driver->id],
                ['balance' => 0.00, 'currency' => 'UZS']
            );


            $driverCommission = round($total * env('SERVICE_FEE_FOR_DRIVERS_FOR_CLIENT_CANCEL_THE_BOOKING') / 100, 2); // 1 %  100000 * 0.01 = 1000
            $driverBalanceBefore = $driverBalance->balance;
            $driverBalance->balance = ((($driverBalance->balance + $cancelationFee) - $total) + $driverCommission); 
            $driverBalance->save();

            $withdrawFromDriver = round($total - $cancelationFee, 2);

            $driverReason =  [
                'uz' => "Foydalanuvchi #{$booking->id} band qilgan safarni bekor qilgan. Haydovchidan  qaytarib olingan  summa: {$withdrawFromDriver} UZS (komissiya: {$driverCommission} UZS) tulab berildi . ",
                'ru' => "Пользователь отменил бронирование #{$booking->id}. Возврат водителю: {$withdrawFromDriver} UZS (комиссия: {$withdrawFromDriver} UZS)",
                'en' => "Booking #{$booking->id} cancelled by user. Driver refund: {$withdrawFromDriver} UZS (fee: {$driverCommission} UZS)",
            ];

            BalanceTransaction::create([
                'user_id' => $driver->id,
                'type' => 'debit',
                'amount' => $withdrawFromDriver,
                'balance_before' => $driverBalanceBefore,
                'balance_after' => $driverBalance->balance,
                'trip_id' => $trip->id,
                'status' => 'success',
                'reason' => $driverReason['uz'],
                'reference_id' => $booking->id,
                'currency' => 'UZS',
            ]);

            // === TRIP SEAT ADJUSTMENT ===
            $trip->available_seats = $trip->available_seats + $booking->seats_booked;
            $trip->status = 'active';
            $trip->save();

            // === BOOKING STATUS UPDATE ===
            $booking->status = 'cancelled';
            $booking->save();

            $companyBalance = CompanyBalance::first();
            $companyBalance->balance = $companyBalance->balance + ( $cancelationFee - $driverCommission);
            $companyBalance->save();

            $cgot = ($cancelationFee - $driverCommission);
            $companyReason = [
                'uz' => "Trip #{$trip->id} cancelled by user. Refund: {$refundForClient} UZS, cancellation fee: {$cancelationFee} UZS, driver compensation: {$driverCommission} UZS and company got $cgot UZS",
                'ru' => "",
                'en' => "",
            ];
                
            CompanyBalanceTransaction::create([
                'company_balance_id' => $companyBalance->id,
                'amount' => ($cancelationFee - $driverCommission),
                'balance_before' => $companyBalance->balance,
                'balance_after' => $companyBalance->balance + ($cancelationFee - $driverCommission),
                'trip_id' => $trip->id,
                'type' => 'income',
                'reason' => $companyReason['uz'],
                'booking_id' => $booking->id,
                'currency' => 'UZS',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully. Refund issued, driver compensated.'
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
