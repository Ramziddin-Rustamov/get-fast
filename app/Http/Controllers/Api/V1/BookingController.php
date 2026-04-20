<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\V1\BookingResource;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use App\Models\V1\Booking;
use App\Models\V1\Trip;
use App\Models\V1\BookingPassengers;
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $bookingService;

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

    public $successResponseForDelete = [
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

    public $successResponse = [
        'uz' => [
            'status' => 'success',
            'message' => 'Hamma malumotlar olib kelindi !'
        ],
        'ru' => [
            'status' => 'success',
            'message' => 'Все данные получены !'
        ],
        'en' => [
            'status' => 'success',
            'message' => 'All data received successfully!'
        ]
    ];


    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index()
    {
        try {
            $bookings = $this->bookingService->getAll();

            return response()->json([
                'status'  => 'success',
                'message' => $this->successResponse[auth()->user()->authLanguage->language ?? 'uz']['message'],
                'data'    => BookingResource::collection($bookings),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $this->errorResponse[auth()->user()->authLanguage->language ?? 'uz'],
                'data'    => null,
            ], 500);
        }
    }


    public function show($id)
    {
        return $this->bookingService->getBookingById($id);
    }

    public function bookTrip(Request $request)
    {


        try {

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


            return $this->bookingService->createBooking($request);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelBooking($id)
    {

        return $this->bookingService->cancelBooking($id);
    }


    public function addPassengerToBooking(Request $request, $bookingId)
    {

        DB::beginTransaction();

        try {
            $lang = auth()->user()->authLanguage->language ?? 'uz';
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);

            $booking = Booking::with('trip')
                ->where('user_id', auth()->id())->lockForUpdate()
                ->find($bookingId);



            if (!$booking) {
                $messages = [
                    'uz' => 'Buyurtma topilmadi',
                    'ru' => 'Бронирование не найдено',
                    'en' => 'Booking not found',
                ];
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$lang] ?? $messages['uz']
                ], 404);
            }


            $trip = Trip::where('id', $booking->trip_id)
                ->lockForUpdate()
                ->first();

            if ($trip->available_seats < 1) {
                $messages = [
                    'uz' => 'Bo‘sh joy yo‘q',
                    'ru' => 'Нет свободных мест',
                    'en' => 'No available seats',
                ];
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$lang] ?? $messages['uz']
                ], 422);
            }

            $price = $trip->price_per_seat;


            $userBalance = UserBalance::lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => auth()->id()],
                    ['balance' => 0]
                );



            if ($userBalance->balance < $price) {
                $messages = [
                    'uz' => 'Balans yetarli emas',
                    'ru' => 'Недостаточно средств',
                    'en' => 'Insufficient balance',
                ];
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$lang] ?? $messages['uz']
                ], 422);
            }

            // ➕ Passenger
            BookingPassengers::create([
                'booking_id' => $booking->id,
                'name' => $request->name,
                'phone' => $request->phone,
            ]);
            $trip->decrement('available_seats');

            $booking->increment('seats_booked');
            $booking->increment('total_price', $price);

            // $trip->available_seats--;
            // $trip->save();
            // $booking->seats_booked++;
            // $booking->increment('seats_booked');
            // $booking->total_price = $booking->total_price  + $price;
            // $booking->save();


            // Trip location names (ID emas, NAME qiymati bilan)
            $startQuarterName = $trip->startQuarter?->name ?? '';
            $endQuarterName   = $trip->endQuarter?->name ?? '';

            $reasonForClient = [
                'uz' => "Siz mavjud buyurtmangizga yana bir yo‘lovchi qo‘shdingiz. Yo‘nalish: {$startQuarterName} dan {$endQuarterName} ga. Sizning balansingizdan {$price} so‘m yechildi va to‘lov amalga oshirildi.",
                'en' => "You added another passenger to your existing booking. Route: from {$startQuarterName} to {$endQuarterName}. {$price} UZS was deducted from your balance and the payment was successfully processed.",
                'ru' => "Вы добавили ещё одного пассажира к существующему бронированию. Маршрут: от {$startQuarterName} до {$endQuarterName}. С вашего баланса списано {$price} сум, оплата успешно выполнена.",
            ];
            // 💰 Client
            BalanceTransaction::create([
                'user_id' => auth()->id(),
                'type' => 'debit',
                'amount' => $price,
                'balance_before' => $userBalance->balance,
                'balance_after' => $userBalance->balance - $price,
                'trip_id' => $trip->id,
                'reference_id' => $booking->id,
                'status' => 'success',
                'reason' => $reasonForClient[$booking->user->authLanguage->language] ?? $reasonForClient['uz'],
            ]);


            $userBalance->decrement('balance', $price);
            // $userBalance->balance = $userBalance->balance - $price;
            // $userBalance->save();

            // 💰 Driver
            $serviceFee = ($price * (config('services.fees.service_fee_for_compliting_order') / 100));  //  5%
            $driverIncome = $price - $serviceFee;

            $driverBalance = UserBalance::where('user_id', $trip->driver_id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $trip->driver_id],
                    ['balance' => 0]
                );

            $reasonForDriver = [
                'uz' => "Sizning mavjud buyurtmangiz uchun yangi yo‘lovchi qo‘shildi va to‘lov amalga oshirildi. Yo‘nalish: {$startQuarterName} dan {$endQuarterName} ga. Sizning balansingizga {$price} so‘m tushdi, {$serviceFee} so‘m xizmat haqi ushlab qolindi.",
                'en' => "A new passenger was added to your existing booking and payment was successfully processed. Route: from {$startQuarterName} to {$endQuarterName}. {$price} UZS was credited to your balance, and a service fee of {$serviceFee} UZS was deducted.",
                'ru' => "К вашему существующему бронированию был добавлен новый пассажир, и оплата успешно выполнена. Маршрут: от {$startQuarterName} до {$endQuarterName}. На ваш баланс зачислено {$price} сум, удержана комиссия за сервис {$serviceFee} сум.",
            ];

            BalanceTransaction::create([
                'user_id' => $trip->driver_id,
                'type' => 'credit',
                'amount' => $driverIncome,
                'balance_before' => $driverBalance->balance,
                'balance_after' => $driverBalance->balance + $driverIncome,
                'trip_id' => $trip->id,
                'reference_id' => $booking->id,
                'status' => 'success',
                'reason' => $reasonForDriver[$trip->driver->authLanguage->language] ?? $reasonForDriver['uz'],
            ]);

            // $driverBalance->balance = $driverBalance->balance + $driverIncome;
            // $driverBalance->save();
            $driverBalance->increment('balance', $driverIncome);


            // 🏢 Company
            $company = CompanyBalance::lockForUpdate()->first();




            // Create Company Balance Transaction
            CompanyBalanceTransaction::create([
                'company_balance_id' => $company->id,
                'amount'             => $serviceFee,
                'balance_before'     => $company->balance,
                'balance_after'      => $company->balance + $serviceFee,
                'trip_id'            => $trip->id,
                'type'               => 'income',
                'reason'             => 'Yangi yo‘lovchi qo‘shildi va kampaniya hisobiga ' . $serviceFee . ' so`m tulov qilindi.' . ' ' . $startQuarterName . ' dan ' . $endQuarterName . ' ga borish uchun mijoz tulov qildi .',
                'booking_id'         => $booking->id,
                'currency'           => 'UZS',
            ]);

            $company->increment('balance', $serviceFee);
            $company->increment('total_income', $serviceFee);


            if ($trip->available_seats == 0) {
                $trip->update(['status' => 'full']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Passenger qo‘shildi, va tulov amalga oshirildi '
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function removePassengerFromBooking(Request $request, $bookingId, $passengerId)
    {
        DB::beginTransaction();

        try {

            $booking = Booking::with('trip')->where('user_id', auth()->id())->find($bookingId);
            $trip = $booking->trip;
            $price = $trip->price_per_seat;


            if (!$booking) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Buyurtma topilmadi'
                ], 404);
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
                    'message' => $cancelTooLate[auth()->user()->authLanguage->language] ?? $cancelTooLate['uz'],
                ], 422);
            }





            $passenger = BookingPassengers::where('booking_id', $booking->id)->find($passengerId);
            if (!$passenger) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Yo‘lovchi topilmadi'
                ], 404);
            }



            // ➕ Yo‘lovchi olib tashlash
            $passenger->delete();

            // ➕ Seat qaytarish
            $trip->available_seats++;
            if ($trip->available_seats > 0) {
                $trip->status = 'active';
            }
            $trip->save();

            $booking->seats_booked--;
            $booking->total_price = $booking->total_price - $price;
            if ($booking->seats_booked == 0) {
                $booking->status = 'cancelled';
            }
            $booking->save();

            // Trip location names
            $startQuarterName = $trip->startQuarter?->name ?? '';
            $endQuarterName   = $trip->endQuarter?->name ?? '';

            // 💰 Client refund
            $userBalance = UserBalance::where('user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => auth()->id()],
                    ['balance' => 0]
                );

            $serviceFee = ($price * (config('services.fees.service_fee_for_compliting_order') / 100));
            $return = ($price - $serviceFee);

            $reasonForClient = [
                'uz' => "Mavjud buyurtmangizdan bir yo‘lovchi olib tashlandi. Yo‘nalish:
                 {$startQuarterName} dan {$endQuarterName} ga. Sizning balansingizga { $return } so‘m qaytarildi. Xizmat haqqi: {$serviceFee} so‘m",
                'en' => "One passenger was removed from your existing booking. Route: from 
                {$startQuarterName} to {$endQuarterName}. { ($return) } UZS was refunded to your balance. Service fee: { ($serviceFee) } UZS",
                'ru' => "Один пассажир был удалён из вашего бронирования. Маршрут: от {$startQuarterName} до 
                {$endQuarterName}. На ваш баланс возвращено { $return } сум. Сервисный сбор: { $serviceFee } сум",
            ];

            BalanceTransaction::create([
                'user_id' => auth()->id(),
                'type' => 'credit',
                'amount' => $return,
                'balance_before' => $userBalance->balance,
                'balance_after' => $userBalance->balance + $return,
                'trip_id' => $trip->id,
                'reference_id' => $booking->id,
                'status' => 'success',
                'reason' => $reasonForClient[$booking->user->authLanguage->language] ?? $reasonForClient['uz'],
            ]);

            $userBalance->balance = $userBalance->balance + $return;
            $userBalance->save();



            // 💰 Driver (minus)
            $driverLoss = $price - $serviceFee;
            $driverCommission = round($price * config('services.fees.service_fee_for_drivers_for_client_cancel_the_booking') / 100, 2); // 1 %  100000 * 0.01 = 1000


            $driverBalance = UserBalance::where('user_id', $trip->driver_id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $trip->driver_id],
                    ['balance' => 0]
                );

            $reasonForDriver = [
                'uz' => "Mavjud buyurtmadan bir yo‘lovchi olib tashlandi. Yo‘nalish: {$startQuarterName} dan {$endQuarterName} ga.
                 Balansingizdan {$driverLoss} so‘m yechildi va {$driverCommission} so‘m  komissiyasi qoldi.",
                'en' => "One passenger was removed from the booking. Route: 
                from {$startQuarterName} to {$endQuarterName}. {$driverLoss} UZS was deducted from your balance and {$driverCommission} UZS commission left.",
                'ru' => "Из бронирования был удалён один пассажир.
                 Маршрут: от {$startQuarterName} до {$endQuarterName}. С вашего баланса списано {$driverLoss} сум и осталась комиссия {$driverCommission} сум",
            ];

            BalanceTransaction::create([
                'user_id' => $trip->driver_id,
                'type' => 'debit',
                'amount' => $driverLoss,
                'balance_before' => $driverBalance->balance,
                'balance_after' => $driverBalance->balance - $driverLoss,
                'trip_id' => $trip->id,
                'reference_id' => $booking->id,
                'status' => 'success',
                'reason' => $reasonForDriver[$trip->driver->authLanguage->language] ?? $reasonForDriver['uz'],
                'created_at' => Carbon::now()->addMinutes(1),
            ]);



            BalanceTransaction::create([
                'user_id' => $trip->driver_id,
                'type' => 'credit',
                'amount' => $driverCommission,
                'balance_before' => $driverBalance->balance,
                'balance_after' => $driverBalance->balance + $driverCommission,
                'trip_id' => $trip->id,
                'reference_id' => $booking->id,
                'status' => 'success',
                'reason' => 'Commission',
                'created_at' => Carbon::now()->addMinutes(2),
            ]);

            $driverBalance->balance = (($driverBalance->balance - $driverLoss) + $driverCommission);
            $driverBalance->save();

            // 🏢 Company (minus service fee)
            $company = CompanyBalance::lockForUpdate()->first();


            CompanyBalanceTransaction::create([
                'company_balance_id' => $company->id,
                'amount'             => $driverCommission, // 1%
                'balance_before'     => $company->balance,
                'balance_after'      => $company->balance - $driverCommission,
                'trip_id'            => $trip->id,
                'type'               => 'outgoing',
                'reason'             => 'Yo‘lovchi mavzjud buyurtmadan olib tashlandi. Xizmat haqining % qismi haydovchiga qaytarildi. ' . $startQuarterName . ' dan ' . $endQuarterName . ' ga.',
                'booking_id'         => $booking->id,
                'currency'           => 'UZS',
            ]);

            $company->decrement('balance', $driverCommission);
            $company->decrement('total_income', $driverCommission);

            DB::commit();

            $messageToResponse = [
                'uz' => "Yo‘lovchi olib tashlandi va hisobingizga pul qaytarildi va komissiya olindi",
                'en' => "One passenger was removed from the booking and the amount was returned to your account and commission was paid",
                'ru' => "Из бронирования был удалён один пассажир и сумма была возвращена в ваш счет и комиссия была оплачена",
            ];



            return response()->json([
                'status' => 'success',
                'message' =>  $messageToResponse[$booking->user->authLanguage->language] ?? $messageToResponse['uz'],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
