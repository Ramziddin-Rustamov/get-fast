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
        'uz' => [
            'status' => 'error',
            'message' => 'Topilmadi!'
        ],
        'ru' => [
            'status' => 'error',
            'message' => 'Не найдено!'
        ],
        'en' => [
            'status' => 'error',
            'message' => 'Not found!'
        ]
    ];

    public $successResponse = [
        'uz' => [
            'status' => 'success',
            'message' => 'Muvaffaqiyatli o‘chirildi!'
        ],
        'ru' => [
            'status' => 'success',
            'message' => 'Удалено успешно!'
        ],
        'en' => [
            'status' => 'success',
            'message' => 'Deleted successfully!'
        ]
    ];




    public function getAllBookings()
    {
        try {
            return Booking::where('user_id', auth()->user()->id)->with('trip', 'trip.vehicle')->paginate(20);
        } catch (\Exception $e) {
            return response()->json($this->errorResponse[auth()->user()->authLanguage->language ?? 'uz'], 404);
        }
    }

    public function getBookingById($id)
    {
        $booking = Booking::with('passengers', 'trip.vehicle', 'trip')->where('user_id', auth()->user()->id)->find($id);

        if (is_null($booking)) {
            return response()->json($this->errorResponse[auth()->user()->authLanguage->language ?? 'uz'], 404);
        }

        return response()->json(new BookingResource($booking), 200);
    }

    public function createBooking($data)
    {
        try {
            $authLan = auth()->user()->authLanguage->language ?? 'uz';


            $trip = Trip::with('vehicle')->find($data['trip_id']);
            if (is_null($trip)) {
                $messages = [
                    'uz' => 'Safar topilmadi',
                    'ru' => 'Поездка не найдена',
                    'en' => 'Trip not found',
                ];
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$authLan] ?? $messages['uz']
                ], 404);
            }

            if ($trip->driver_id == auth()->user()->id) {
                $messages = [
                    'uz' => 'Siz o‘zingizning safaringizni band qila olmaysiz',
                    'ru' => 'Вы не можете забронировать свою поездку',
                    'en' => 'You cannot book your own trip',
                ];
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$authLan] ?? $messages['uz']
                ]);
            }

            $requestedSeats = count($data['passengers']);



            DB::beginTransaction();
            if ($requestedSeats > $trip->available_seats) {
                $messages = [
                    'uz' => 'Etarli joy mavjud emas',
                    'ru' => 'Недостаточно мест',
                    'en' => 'Not enough seats available',
                ];
                return response()->json(['status' => 'error', 'message' => $messages[$authLan] ?? $messages['uz']], 422);
            }

            if ($trip->status == 'cancelled') {
                $messages = [
                    'uz' => 'Safar bekor qilingan',
                    'ru' => 'Поездка уже отменена',
                    'en' => 'Trip already is cancelled',
                ];
                return response()->json(['status' => 'error', 'message' => $messages[$authLan] ?? $messages['uz']]);
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
                ]);
            }

            if (!$driverBalance) {
                $driverBalance = UserBalance::create([
                    'user_id' => $trip->driver_id,
                    'balance' => '00.00',
                ]);
            }



            $totalPrice = $trip->price_per_seat * $requestedSeats;
            $totalPrice = number_format((float)$totalPrice, 2, '.', '');
            //


            if ($userBalance->balance < $totalPrice) {
                $messages = [
                    'uz' => 'Booking uchun yetarli balans yo‘q',
                    'ru' => 'Недостаточно средств для бронирования',
                    'en' => 'Insufficient balance for booking',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$authLan] ?? $messages['uz']
                ], 422);
            }


            $serviceFeePercent =  env('SERVICE_FEE_FOR_COMPLITING_ORDER'); // 5
            if(!$serviceFeePercent){
                $serviceFeePercent = 5;
            }
            $serviceFee = $totalPrice * ($serviceFeePercent / 100); // 400000 * 5% = 200000
            $serviceFee = number_format((float)$serviceFee, 2, '.', ''); // 20.000

            $net_income = $totalPrice - $serviceFee; // for driver 400000 - 20.000 = 380000
            $net_income = number_format((float)$net_income, 2, '.', ''); // 380000



            $booking = Booking::create([
                'trip_id' => $data['trip_id'],
                'user_id' => auth()->user()->id,
                'seats_booked' => $requestedSeats,
                'total_price' => $totalPrice,
                'status' => 'confirmed',
                'expired_at' => $trip->end_time
            ]);

            if ($driverBalance) {

                $startQuarterName = $trip->startQuarter->name ?? '';
                $endQuarterName = $trip->endQuarter->name ?? '';

                $reasonForDriver = [
                    'uz' => "Siz yangi booking oldingiz (Booking ID: $booking->id) haydovchining safari uchun (Trip: $startQuarterName → $endQuarterName).
                     Jami tushum: $net_income UZS. Service fee: $serviceFee UZS. Umumiy daromad: $totalPrice UZS.",
                    'en' => "You received a new booking (Booking ID: $booking->id) for your trip
                     (Trip: $startQuarterName → $endQuarterName). Total received: $net_income UZS.
                      Service fee: $serviceFee UZS. Overall earnings: $totalPrice UZS.",
                    'ru' => "Вы получили новое бронирование (Booking ID: $booking->id)
                     для вашей поездки (Trip: $startQuarterName → $endQuarterName). 
                     Получено всего: $net_income UZS. Комиссия: $serviceFee UZS. Общий доход: $totalPrice UZS."
                ];


                $driverBalanceTransaction = new BalanceTransaction();
                $driverBalanceTransaction->user_id = $trip->driver_id;
                $driverBalanceTransaction->type = 'credit';
                $driverBalanceTransaction->amount = $net_income;
                $driverBalanceTransaction->balance_before = $driverBalance->balance;
                $driverBalanceTransaction->balance_after = $driverBalance->balance + $net_income;
                $driverBalanceTransaction->trip_id = $trip->id;
                $driverBalanceTransaction->status = 'success';
                $driverBalanceTransaction->reason = $reasonForDriver[$trip->driver->authLanguage->language ?? 'uz'];
                $driverBalanceTransaction->reference_id = $booking->id;
                $driverBalanceTransaction->save();

                $driverBalance->balance = ($driverBalance->balance) + ($net_income);
                $driverBalance->save();
            }

            if ($userBalance) {

                $startQuarterName = $trip->startQuarter->name ?? '';
                $endQuarterName = $trip->endQuarter->name ?? '';

                $reasonForClient = [
                    'uz' => "Siz yangi booking qildingiz (Booking ID: $booking->id) safari uchun (Safari: $startQuarterName → $endQuarterName). Umumiy summa: $totalPrice UZS",
                    'en' => "You made a new booking (Booking ID: $booking->id) for your trip (Trip: $startQuarterName → $endQuarterName). Total price: $totalPrice UZS",
                    'ru' => "Вы сделали новое бронирование (Booking ID: $booking->id) для вашей поездки (Trip: $startQuarterName → $endQuarterName). Общая сумма: $totalPrice UZS"
                ];


                $clientBalanceTranaction = new BalanceTransaction();
                $clientBalanceTranaction->user_id = auth()->user()->id;
                $clientBalanceTranaction->type = 'debit';
                $clientBalanceTranaction->amount = $totalPrice;
                $clientBalanceTranaction->balance_before = $userBalance->balance;
                $clientBalanceTranaction->balance_after = $userBalance->balance - $totalPrice;

                $clientBalanceTranaction->trip_id = $trip->id;
                $clientBalanceTranaction->status = 'success';
                $clientBalanceTranaction->reason = $reasonForClient[$authLan] ?? $reasonForClient['uz'];
                $clientBalanceTranaction->reference_id = $booking->id;
                $clientBalanceTranaction->save();

                $userBalance->balance = ($userBalance->balance) - ($totalPrice);
                $userBalance->save();
            }

            if ($companyBalance) {

                $startQuarterName = $trip->startQuarter->name ?? '';
                $endQuarterName = $trip->endQuarter->name ?? '';

                $reasonCompany = [
                    'uz' => "Mijoz yangi booking qildi (Booking ID: $booking->id) haydovchi safari uchun (Trip: $startQuarterName → $endQuarterName). Umumiy summa: $totalPrice UZS, Service fee: $serviceFee UZS +",
                    'en' => "Client made a new booking (Booking ID: $booking->id) for driver trip (Trip: $startQuarterName → $endQuarterName). Total price: $totalPrice UZS, Service fee: $serviceFee UZS",
                    'ru' => "Клиент сделал новое бронирование (Booking ID: $booking->id) для поездки водителя (Trip: $startQuarterName → $endQuarterName). Общая сумма: $totalPrice UZS, Service fee: $serviceFee UZS"
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
            return response()->json(['status' => 'error', 'message' => 'Booking creation failed: ' . $e->getMessage()], 500);
        }
    }


    public function updateBooking($id, array $data)
    {


        try {

            $booking = Booking::where('user_id', auth()->user()->id)->find($id);

            if (is_null($booking)) {
                return response()->json($this->errorResponse[auth()->user()->authLanguage->language ?? 'uz'], 404);
            }

            DB::beginTransaction();

            $requestedSeats = isset($data['passengers']) ? count($data['passengers']) : $booking->seats_booked;
            //                                         2                       1
            if ($requestedSeats > $booking->seats_booked) {
                $messages = [
                    'uz' => 'Siz oldin band qilgan yo‘lovchilardan ko‘p yo‘lovchi qo‘sholmaysiz',
                    'ru' => 'Вы не можете добавить больше пассажиров, чем уже забронировано',
                    'en' => 'You cannot add more passengers than you have already booked',
                ];
                $language = auth()->user()->authLanguage->language ?? 'uz';
                $message = $messages[$language];

                return response()->json([
                    'status' => 'error',
                    'message' => $message
                ], 422);
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
            return response()->json([
                'status' => 'error',
                'messsage' => 'Server error occurred'
            ], 500);
        }
    }

    public function cancelBooking($bookingId)
    {


        try {
            DB::beginTransaction();
            $user = Auth::user();

            $authLan = $user->authLanguage->language ?? 'uz';

            // Booking qidirish
            $booking = Booking::where('user_id', $user->id)->find($bookingId);

            if (!$booking) {

                $messages = [
                    'uz' => 'Buyurtma topilmadi',
                    'en' => 'Booking not found',
                    'ru' => 'Бронирование не найдено',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$authLan] ?? $messages['uz']
                ]);
            }





            if (in_array($booking->status, ['cancelled', 'pending', 'completed'])) {

                $messages = [
                    'uz' => 'Bu bosqichda buyurtmani bekor qilib bo‘lmaydi. (cancelled, pending, completed)',
                    'en' => 'Booking cannot be cancelled at this stage. (cancelled, pending, completed)',
                    'ru' => 'На этом этапе бронирование нельзя отменить. (cancelled, pending, completed)',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$authLan] ?? $messages['uz'],
                ], 422);
            }


            $trip = Trip::find($booking->trip_id);
            if (!$trip) {

                $tripNotFound = [
                    'uz' => 'Safar topilmadi.',
                    'en' => 'Trip not found.',
                    'ru' => 'Поездка не найдена.',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $tripNotFound[$authLan] ?? $tripNotFound['uz'],
                ]);
            }

            // ❗ Booking cancel 2 soatdan oldin bo'lishi kerak
            $tripStart = \Carbon\Carbon::parse($trip->start_time);
            $now = \Carbon\Carbon::now();

            if ($now->greaterThanOrEqualTo($tripStart->subHours(2))) {

                $cancelTooLate = [
                    'uz' => 'Safar boshlanishiga 2 soatdan kam vaqt qolgani uchun bekor qilish mumkin emas.',
                    'en' => 'Cancellation is not allowed because less than 2 hours remain before the trip starts.',
                    'ru' => 'Отмена невозможна, так как до начала поездки осталось менее 2 часов.',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $cancelTooLate[$authLan] ?? $cancelTooLate['uz'],
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


            $start = $trip->startQuarter->name ?? 'Nomaʼlum';
            $end   = $trip->endQuarter->name ?? 'Nomaʼlum';

            $reasonForClientCancelation = [
                'uz' => "Foydalanuvchi #{$booking->id} band qilgan safarni bekordi. Yo‘nalish: {$start} → {$end}. Qaytarilgan summa: {$refundForClient} UZS, bekor qilish komissiyasi: {$cancelationFee} UZS.",
                'ru' => "Пользователь отменил бронирование #{$booking->id}. Маршрут: {$start} → {$end}. Возврат: {$refundForClient} UZS, комиссия за отмену: {$cancelationFee} UZS.",
                'en' => "User cancelled booking #{$booking->id}. Route: {$start} → {$end}. Refund: {$refundForClient} UZS, cancellation fee: {$cancelationFee} UZS.",
            ];

            BalanceTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $refundForClient,
                'balance_before' => $userBalance->balance,
                'balance_after' => $userBalance->balance + $refundForClient,
                'trip_id' => $trip->id,
                'status' => 'success',
                'reason' => $reasonForClientCancelation[$authLan] ?? $reasonForClientCancelation['uz'],
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

            $start = $trip->startQuarter->name ?? 'Nomaʼlum';
            $end   = $trip->endQuarter->name ?? 'Nomaʼlum';
            $driverReason = [
                'uz' => "Foydalanuvchi #{$booking->id} band qilgan safarni bekor qildi. Yo‘nalish: {$start} → {$end}. Haydovchidan qaytarib olingan summa: {$withdrawFromDriver} UZS (komissiya: {$driverCommission} UZS).",
                'ru' => "Пользователь отменил бронирование #{$booking->id}. Маршрут: {$start} → {$end}. С водителя удержано: {$withdrawFromDriver} UZS (комиссия: {$driverCommission} UZS).",
                'en' => "User cancelled booking #{$booking->id}. Route: {$start} → {$end}. Amount deducted from driver: {$withdrawFromDriver} UZS (fee: {$driverCommission} UZS).",
            ];


            BalanceTransaction::create([
                'user_id' => $driver->id,
                'type' => 'debit',
                'amount' => $withdrawFromDriver,
                'balance_before' => $driverBalanceBefore,
                'balance_after' => $driverBalance->balance,
                'trip_id' => $trip->id,
                'status' => 'success',
                'reason' => $driverReason[$driver->authLanguage->language] ?? $driverReason['uz'],
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
            $companyBalance->balance = $companyBalance->balance + ($cancelationFee - $driverCommission);
            $companyBalance->save();

            // Company got amount
            $cgot = $cancelationFee - $driverCommission;

            // Trip location names (ID emas, NAME qiymati bilan)
            $startQuarterName = $trip->startQuarter?->name ?? '';
            $endQuarterName   = $trip->endQuarter?->name ?? '';

            // Multi–language reason
            $companyReason = [
                'uz' => "Foydalanuvchi trip #{$trip->id} safarni bekor qildi. Boshlanish nuqtasi: {$startQuarterName}, borish nuqtasi: {$endQuarterName}. Mijozga qaytarilgan summa: {$refundForClient} UZS, bekor qilish komissiyasi: {$cancelationFee} UZS, haydovchi kompensatsiyasi: {$driverCommission} UZS. Kompaniya olgan sof summa: {$cgot} UZS.",

                'ru' => "Пользователь отменил поездку #{$trip->id}. От: {$startQuarterName}, До: {$endQuarterName}. Возврат клиенту: {$refundForClient} UZS, комиссия: {$cancelationFee} UZS, компенсация водителю: {$driverCommission} UZS. Чистая прибыль компании: {$cgot} UZS.",

                'en' => "User cancelled trip #{$trip->id}. From: {$startQuarterName}, To: {$endQuarterName}. Client refund: {$refundForClient} UZS, cancellation fee: {$cancelationFee} UZS, driver compensation: {$driverCommission} UZS. Company earned: {$cgot} UZS.",
            ];

            // Create Company Balance Transaction
            CompanyBalanceTransaction::create([
                'company_balance_id' => $companyBalance->id,
                'amount'             => $cgot,
                'balance_before'     => $companyBalance->balance,
                'balance_after'      => $companyBalance->balance + $cgot,
                'trip_id'            => $trip->id,
                'type'               => 'income',
                'reason'             => $companyReason['uz'], // change to selected language if needed
                'booking_id'         => $booking->id,
                'currency'           => 'UZS',
            ]);

            DB::commit();

            $messages = [
                'uz' => 'Bron bekor qilindi. Mijozga pul qaytarildi, haydovchi kompensatsiya oldi.',
                'ru' => 'Бронирование успешно отменено. Клиенту возвращены средства, водитель получил компенсацию.',
                'en' => 'Booking cancelled successfully. Refund issued, driver compensated.',
            ];

            return response()->json([
                'status' => 'success',
                'message' => $messages[$authLan] ?? $messages['uz'],
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
