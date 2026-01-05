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
use Carbon\Carbon;
use App\Services\V1\SmsService;

class BookingRepository
{

    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

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

        return [
            'status' => 'success',
            'message' => 'Booking fetched successfully',
            'data' => new BookingResource($booking)
        ];
    }

    public function createBooking($data)
    {
        try {
            $authLan = auth()->user()->authLanguage->language ?? 'uz';


            $trip = Trip::with('vehicle')->find($data['trip_id']);
            if (is_null($trip)) {
                $messages = [
                    'uz' => 'Safar topilmadi, qayta urinib ko‘ring',
                    'ru' => 'Поездка не найдена, повторите попытку',
                    'en' => 'Trip not found, try again',
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
                    'uz' => 'yetarli joy mavjud emas, qayta urinib ko‘ring',
                    'ru' => 'Недостаточно мест, повторите попытку',
                    'en' => 'Not enough seats available, try again',
                ];
                return response()->json(['status' => 'error', 'message' => $messages[$authLan] ?? $messages['uz']], 422);
            }

            if ($trip->status == 'cancelled') {
                $messages = [
                    'uz' => 'Safar allaqachon bekor qilingan',
                    'ru' => 'Поездка уже отменена',
                    'en' => 'Trip already is cancelled',
                ];
                return response()->json(['status' => 'error', 'message' => $messages[$authLan] ?? $messages['uz']]);
            }



            // User balance
            $userBalance = UserBalance::where('user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);

            // Driver balance
            $driverBalance = UserBalance::where('user_id', $trip->driver_id)
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => $trip->driver_id], ['balance' => 0]);

            // Company balance
            $companyBalance = CompanyBalance::lockForUpdate()->first();
            if (!$companyBalance) {
                $companyBalance = CompanyBalance::create(['balance' => 0]);
            }



            // Trip available seats update
            $trip->available_seats -= $requestedSeats;
            if ($trip->available_seats <= 0) {
                $trip->status = 'full';
            }
            $trip->save();

            $totalPrice = $trip->price_per_seat * $requestedSeats;
            $totalPrice = number_format((float)$totalPrice, 2, '.', '');
            //


            if ($userBalance->balance < $totalPrice) {
                $messages = [
                    'uz' => 'Buyurtma uchun yetarli balans mavjud emas',
                    'ru' => 'Недостаточно средств для бронирования',
                    'en' => 'Insufficient balance for booking',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$authLan] ?? $messages['uz']
                ], 422);
            }


            $serviceFeePercent =  config('services.fees.service_fee_for_compliting_order'); // 5
            if (!$serviceFeePercent) {
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



                $textSMSForDriver = [
                'uz' => "Qadam ilovasida siz yangi buyurtma oldingiz. Umumiy summa: $totalPrice UZS, sizga tushadigan summa: $net_income UZS. Iltimos, buyurtmani bajarib bo'lgungizcha hisobingizdagi summani yechmang. 
                Ilovadan batafsil tekshiring. Mijoz: {$booking->user->first_name} {$booking->user->last_name}, telefon raqami: {$booking->user->phone}, band qilingan joylar: $requestedSeats ta.",
                'ru' => "В приложении Qadam у вас новый заказ. Общая сумма: $totalPrice UZS, вам поступит: $net_income UZS. Пожалуйста, не снимайте деньги с вашего счета, пока заказ не будет выполнен. 
                Подробности можно проверить в приложении. Клиент: {$booking->user->first_name} {$booking->user->last_name}, телефон: {$booking->user->phone}, забронированные места: $requestedSeats.",
                'en' => "You have a new order in the Qadam app. Total amount: $totalPrice UZS, your net income: $net_income UZS. Please do not withdraw the amount from your account until the order is completed. 
                Check details in the app. Customer: {$booking->user->first_name} {$booking->user->last_name}, phone: {$booking->user->phone}, seats booked: $requestedSeats."
                ];

                $textSMSForDriver = $textSMSForDriver[$trip->driver->authLanguage->language] ?? $textSMSForDriver['uz'];
                if($trip->driver->phone){
                // SMS jo'natish
                // $this->smsService->sendQueued($trip->driver->phone, $textSMSForDriver, 'send-sms-to-driver-about-new-booking');
                }


            }

            if ($userBalance) {

                $startQuarterName = $trip->startQuarter->name ?? '';
                $endQuarterName = $trip->endQuarter->name ?? '';

                $reasonForClient = [
                    'uz' => "Siz yangi booking qildingiz (Booking ID: $booking->id) safari uchun (Safari: $startQuarterName → $endQuarterName). Umumiy summa: $totalPrice  UZS",
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

                $textSMSForClient = [
                'uz' => "Qadam ilovasida siz yangi buyurtma qildingiz. Umumiy summa: $totalPrice UZS to‘landi. 
                Haydovchi: {$trip->driver->first_name} {$trip->driver->last_name}, telefon raqami: {$trip->driver->phone}, band qilingan joylar: $requestedSeats ta. 
                Sayohatni boshlashdan oldin, iltimos haydovchingiz bilan bog‘laning.",

                'ru' => "В приложении Qadam вы сделали новый заказ. Общая сумма: $totalPrice UZS оплачена. 
                Водитель: {$trip->driver->first_name} {$trip->driver->last_name}, телефон: {$trip->driver->phone}, забронированные места: $requestedSeats. 
                Перед началом поездки, пожалуйста, свяжитесь с вашим водителем.",

                'en' => "You have made a new booking in the Qadam app. Total amount: $totalPrice UZS has been paid. 
                Driver: {$trip->driver->first_name} {$trip->driver->last_name}, phone: {$trip->driver->phone}, seats booked: $requestedSeats. 
                Before starting the trip, please contact your driver."
                ];

                $textSMSForClient = $textSMSForClient[$booking->user->authLanguage->language] ?? $textSMSForClient['uz'];
                // SMS jo'natish
                if($booking->user->phone)
                {
                    // $this->smsService->sendQueued($booking->user->phone, $textSMSForClient, 'send-sms-to-driver-about-new-booking');
                }
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


            if (in_array($booking->status, ['cancelled', 'pending', 'completed']) || in_array($booking->trip->status, ['cancelled', 'pending', 'completed'])) {

                $messages = [
                    'uz' => 'Bu bosqichda buyurtmani bekor qilib bo‘lmaydi. ( bkor qilingan, ushlab turilgan, yakunlangan)',
                    'en' => 'Booking cannot be cancelled at this stage. (cancelled, pending, completed)',
                    'ru' => 'На этом этапе бронирование нельзя отменить. (отменено, ожидается, завершено)',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$authLan] ?? $messages['uz'],
                ], 422);
            }


            $trip = Trip::find($booking->trip_id);
            if (!$trip) {

                $tripNotFound = [
                    'uz' => 'Safar topilmadi, eltimos qaytadan urinib ko‘ring',
                    'en' => 'Trip not found, try again later',
                    'ru' => 'Поездка не найдена, попробуйте позже',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $tripNotFound[$authLan] ?? $tripNotFound['uz'],
                ]);
            }


            $now = Carbon::now(); // Carbon obyekt
            $startTime = Carbon::parse($trip->start_time); // Carbon obyekt

            $startStr = $startTime->toDateTimeString();

            $hoursDiff = $now->diffInHours($startStr, false);


            // 2 soatdan kam qolgan bo'lsa bekor qilish mumkin emas
            if ($hoursDiff < 2) {
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
            $cancelationFee = round($total * config('services.fees.service_fee_for_canceling_order') / 100, 2); // 100000 * 0.5 = 5000
            $refundForClient = round($total - $cancelationFee, 2);


            // === USER BALANCE UPDATE ===
            $userBalance = UserBalance::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0.00, 'currency' => 'UZS']
                );

            $userBalance->increment('balance', $refundForClient);


            $start = $trip->startQuarter->name ?? 'Nomaʼlum';
            $end   = $trip->endQuarter->name ?? 'Nomaʼlum';

            $reasonForClientCancelation = [
                'uz' => "Siz #{$booking->id} band qilgan safarni bekor qildi. Yo‘nalish: {$start} → {$end}. Qaytarilgan summa: {$refundForClient} UZS, bekor qilish komissiyasi: {$cancelationFee} UZS.",
                'ru' => "Пользователь отменил бронирование #{$booking->id}. Маршрут: {$start} → {$end}. Возврат: {$refundForClient} UZS, комиссия за отмену: {$cancelationFee} UZS.",
                'en' => "User cancelled booking #{$booking->id}. Route: {$start} → {$end}. Refund: {$refundForClient} UZS, cancellation fee: {$cancelationFee} UZS.",
            ];

            $textSMSMessageToClientAboutCancelation = [
                'uz' => "Siz qadam ilovasida band qilgan safar bekor qildingiz. Yo‘nalish: {$start} → {$end}. Qaytarilgan summa: {$refundForClient} UZS, bekor qilish komissiyasi: {$cancelationFee} UZS.",
                'ru' => "Вы отменили поездку #{$booking->id}. Маршрут: {$start} → {$end}. Возврат: {$refundForClient} UZS, комиссия за отмену: {$cancelationFee} UZS.",
                'en' => "You cancelled the trip #{$booking->id}. Route: {$start} → {$end}. Refund: {$refundForClient} UZS, cancellation fee: {$cancelationFee} UZS.",
            ];
            $textSMSMessageToClientAboutCancelation = $textSMSMessageToClientAboutCancelation[$authLan] ?? $textSMSMessageToClientAboutCancelation['uz'];

              if($user->phone){
                // $this->smsService->sendQueued($user->phone, $textSMSMessageToClientAboutCancelation, 'send-sms-to-client-about-order-cancelation');
              }

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

            $driverBalance = UserBalance::where('user_id', $driver->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $driver->id],
                    ['balance' => 0.00, 'currency' => 'UZS']
                );


            $driverCommission = round($total * config('services.fees.service_fee_for_drivers_for_client_cancel_the_booking') / 100, 2); // 1 %  100000 * 0.01 = 1000
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


            $textSMSMessageToDriverAboutCancelation = [
                'uz' => "Mijoz {$booking->user->first_name} band qilgan safarni bekor qildi. Yo‘nalish: {$start} → {$end}. Sizdan qaytarib olingan summa: {$withdrawFromDriver} UZS (sizga qo‘shimcha to‘lab berildi: {$driverCommission} UZS).",
                'ru' => "Клиент {$booking->user->first_name} отменил забронированную поездку. Маршрут: {$start} → {$end}. Сумма, возвращённая с вас: {$withdrawFromDriver} UZS (вам дополнительно выплачено: {$driverCommission} UZS).",
                'en' => "Customer {$booking->user->first_name} has cancelled the booked trip. Route: {$start} → {$end}. Amount withdrawn from you: {$withdrawFromDriver} UZS (additional payment made to you: {$driverCommission} UZS)."
            ];
            $textSMSMessageToDriverAboutCancelation = $textSMSMessageToDriverAboutCancelation[$authLan] ?? $textSMSMessageToDriverAboutCancelation['uz'];

              if($trip->driver->phone){
                // $this->smsService->sendQueued($trip->driver->phone, $textSMSMessageToDriverAboutCancelation, 'send-sms-to-client-about-order-cancelation');
              }


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

            $companyBalance = CompanyBalance::lockForUpdate()->firstOrFail();

            // Company got amount
            $cgot = $cancelationFee - $driverCommission;

            // Trip location names (ID emas, NAME qiymati bilan)
            $startQuarterName = $trip->startQuarter?->name ?? '';
            $endQuarterName   = $trip->endQuarter?->name ?? '';

            // Multi–language reason
            $companyReason = [
                'uz' => "Foydalanuvchi trip #{$trip->id} safarni bekor qildi. Boshlanish nuqtasi: {$startQuarterName}, borish nuqtasi: {$endQuarterName}.
                 Mijozga qaytarilgan summa: {$refundForClient} UZS, bekor qilish komissiyasi: {$cancelationFee} UZS, haydovchi kompensatsiyasi: {$driverCommission} UZS.
                  Kompaniya olgan sof summa: {$cgot} UZS. va faqat biz kampaniya hisobidan havdovchiga qaytarib beryapmiz summa: {$driverCommission} UZS.",
                'ru' => "Пользователь отменил поездку #{$trip->id}. От: {$startQuarterName}, До: {$endQuarterName}. Возврат клиенту: {$refundForClient} UZS, комиссия:
                 {$cancelationFee} UZS, компенсация водителю: {$driverCommission} UZS. Чистая прибыль компании: {$cgot} UZS. Только мы возвращаем водителю: {$driverCommission} UZS.",
                'en' => "User cancelled trip #{$trip->id}. From: {$startQuarterName}, To: {$endQuarterName}. Client refund: {$refundForClient} UZS, cancellation fee: 
                {$cancelationFee} UZS, driver compensation: {$driverCommission} UZS. Company earned: {$cgot} UZS. Only we return to the driver: {$driverCommission} UZS.",
            ];

            // Create Company Balance Transaction
            CompanyBalanceTransaction::create([
                'company_balance_id' => $companyBalance->id,
                'amount'             => $driverCommission,
                'balance_before'     => $companyBalance->balance,
                'balance_after'      => $companyBalance->balance - $driverCommission,
                'trip_id'            => $trip->id,
                'type'               => 'outgoing',
                'reason'             => $companyReason['uz'], // change to selected language if needed
                'booking_id'         => $booking->id,
                'currency'           => 'UZS',
            ]);
            $companyBalance->balance = $companyBalance->balance - $driverCommission;
            $companyBalance->save();

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
