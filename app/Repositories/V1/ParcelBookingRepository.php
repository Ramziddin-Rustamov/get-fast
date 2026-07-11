<?php

namespace App\Repositories\V1;

use App\Models\V1\Trip;
use App\Models\V1\ParcelBooking;
use App\Models\UserBalance;
use App\Models\BalanceTransaction;
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;
use App\Http\Resources\V1\ParcelBookingResource;
use App\Jobs\SendPushNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ParcelBookingRepository
{
    protected function lang(): string
    {
        return auth()->user()->authLanguage->language ?? 'uz';
    }

    protected function pick(array $messages): string
    {
        return $messages[$this->lang()] ?? $messages['uz'];
    }

    protected function error(array $messages, int $code)
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->pick($messages),
            'data' => null,
        ], $code);
    }

    protected function ok(array $messages, $data = null, int $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $this->pick($messages),
            'data' => $data,
        ], $code);
    }

    // ============================= CLIENT =============================

    /**
     * Mijoz safarga posilka yuborish so'rovini yaratadi.
     */
    public function createBooking(array $data)
    {
        try {
            DB::beginTransaction();

            $trip = Trip::with('parcel.types', 'startQuarter', 'endQuarter')
                ->where('id', $data['trip_id'])
                ->lockForUpdate()
                ->first();

            if (!$trip) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Safar topilmadi',
                    'ru' => 'Поездка не найдена',
                    'en' => 'Trip not found',
                ], 404);
            }

            // Safar pochta qabul qiladimi? (nofaollashtirilgan bo'lsa ham qabul qilmaydi)
            $parcel = $trip->parcel;
            if (!$parcel || !$parcel->is_active) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Bu safar pochta qabul qilmaydi',
                    'ru' => 'Эта поездка не принимает посылки',
                    'en' => 'This trip does not accept parcels',
                ], 422);
            }

            // O'z safariga posilka yubora olmaydi
            if ($trip->driver_id == auth()->id()) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'O‘z safaringizga posilka yubora olmaysiz',
                    'ru' => 'Вы не можете отправить посылку в свою поездку',
                    'en' => 'You cannot send a parcel to your own trip',
                ], 422);
            }

            // Safar hali faolmi?
            if (!in_array($trip->status, ['active', 'full'])) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Safar faol emas',
                    'ru' => 'Поездка неактивна',
                    'en' => 'Trip is not active',
                ], 422);
            }

            // Safar allaqachon boshlanmaganmi?
            if (Carbon::now()->greaterThanOrEqualTo(Carbon::parse($trip->start_time))) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Safar boshlangani uchun posilka qabul qilinmaydi',
                    'ru' => 'Поездка уже началась, посылка не принимается',
                    'en' => 'The trip has already started, parcels are not accepted',
                ], 422);
            }

            // Tanlangan tur shu safar qabul qiladigan turlar ichidami?
            $typeAccepted = $parcel->types->contains('id', (int) $data['parcel_type_id']);
            if (!$typeAccepted) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Bu safar tanlangan pochta turini qabul qilmaydi',
                    'ru' => 'Эта поездка не принимает выбранный тип посылки',
                    'en' => 'This trip does not accept the selected parcel type',
                ], 422);
            }

            // Sig'im chegarasi — qolgan bo'sh og'irlik (available_weight)
            $available = $parcel->available_weight ?? $parcel->max_weight;
            if (!is_null($available) && $data['weight'] > $available) {
                DB::rollBack();
                return $this->error([
                    'uz' => "Safarda faqat {$available} kg bo‘sh joy qoldi",
                    'ru' => "В поездке осталось только {$available} кг свободного места",
                    'en' => "Only {$available} kg of free space is left on this trip",
                ], 422);
            }

            // O'lcham chegaralari (agar haydovchi kiritgan bo'lsa)
            if ($this->exceedsDimensions($parcel, $data)) {
                DB::rollBack();
                return $this->error([
                    'uz' => "Posilka o‘lchami haydovchi bagajiga sig‘maydi (maks: {$parcel->max_length}×{$parcel->max_width}×{$parcel->max_height} sm)",
                    'ru' => "Размер посылки не помещается в багажник (макс: {$parcel->max_length}×{$parcel->max_width}×{$parcel->max_height} см)",
                    'en' => "Parcel dimensions exceed the driver's capacity (max: {$parcel->max_length}×{$parcel->max_width}×{$parcel->max_height} cm)",
                ], 422);
            }

            // Narx = og'irlik × kg narxi
            $pricePerKg = $parcel->price_per_kg ?? 0;
            $totalPrice = number_format((float) ($data['weight'] * $pricePerKg), 2, '.', '');

            // Mijoz balansini tekshiramiz
            $userBalance = UserBalance::where('user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);

            if ($userBalance->balance < $totalPrice) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Posilka uchun balansingiz yetarli emas',
                    'ru' => 'Недостаточно средств для посылки',
                    'en' => 'Insufficient balance for the parcel',
                ], 422);
            }

            $booking = ParcelBooking::create([
                'parcel_id' => $parcel->id,
                'trip_id' => $trip->id,
                'parcel_type_id' => $data['parcel_type_id'],
                'user_id' => auth()->id(),
                'receiver_phone' => $data['receiver_phone'],
                'pickup_lat' => $data['pickup_lat'] ?? null,
                'pickup_long' => $data['pickup_long'] ?? null,
                'dropoff_lat' => $data['dropoff_lat'] ?? null,
                'dropoff_long' => $data['dropoff_long'] ?? null,
                'parcel_description' => $data['parcel_description'] ?? null,
                'weight' => $data['weight'],
                'length' => $data['length'] ?? null,
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'total_price' => $totalPrice,
                // Haydovchi trip yaratishда posilka olishga allaqachon rozi bo'lgan —
                // qo'shimcha tasdiq kerak emas, so'rov darhol qabul qilinadi.
                'status' => 'confirmed',
                'expired_at' => $trip->end_time,
            ]);

            // Sig'imni kamaytiramiz (trip lockForUpdate bilan serializatsiya qilingan)
            $parcel->decrement('available_weight', (float) $data['weight']);

            // Pul harakati: mijozdan yechish, driverga (netto), kompaniyaga (xizmat haqi)
            $this->applyCharge($userBalance, $trip, $booking, (float) $totalPrice);

            DB::commit();

            // Haydovchiga Firebase push (queue orqali — so'rovni bloklamaydi).
            SendPushNotification::dispatch($trip->driver_id, 'parcel.new', [
                'from' => $trip->startQuarter->name ?? '',
                'to' => $trip->endQuarter->name ?? '',
                'weight' => $data['weight'],
            ], [
                'trip_id' => (string) $trip->id,
                'parcel_booking_id' => (string) $booking->id,
            ]);

            return $this->ok([
                'uz' => 'Posilka qabul qilindi',
                'ru' => 'Посылка принята',
                'en' => 'Parcel accepted',
            ], new ParcelBookingResource($booking->load('trip', 'type', 'user')), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $this->pick([
                    'uz' => 'Posilka so‘rovini yaratishda xatolik yuz berdi',
                    'ru' => 'Ошибка при создании запроса на посылку',
                    'en' => 'Error occurred while creating the parcel request',
                ]),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function exceedsDimensions($parcel, array $data): bool
    {
        foreach (['length', 'width', 'height'] as $dim) {
            $max = $parcel->{'max_' . $dim};
            $val = $data[$dim] ?? null;
            if (!is_null($max) && !is_null($val) && $val > $max) {
                return true;
            }
        }
        return false;
    }

    protected function serviceFeePercent(): float
    {
        return (float) (config('services.fees.service_fee_for_compliting_order') ?: 5);
    }

    /**
     * Admin/tashqi chaqiruv uchun: posilkani bekor qilib, to'lovni qaytaradi.
     * DB tranzaksiyasi ichida, $trip bloklangan holda chaqirilishi kerak.
     * $trip startQuarter/endQuarter bilan yuklangan bo'lishi kerak.
     */
    public function forceCancelWithRefund(ParcelBooking $booking, Trip $trip): void
    {
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return;
        }

        $wasConfirmed = $booking->status === 'confirmed';
        $booking->update(['status' => 'cancelled']);

        if ($wasConfirmed) {
            $this->applyRefund($trip, $booking);
        }
    }

    /**
     * Posilka to'lovi: mijozdan yechish, driverga (netto), kompaniyaga (xizmat haqi).
     * Chaqirilishidan oldin balans yetarliligi tekshirilgan bo'lishi kerak.
     */
    protected function applyCharge(UserBalance $userBalance, Trip $trip, ParcelBooking $booking, float $totalPrice): void
    {
        $serviceFee = round($totalPrice * $this->serviceFeePercent() / 100, 2);
        $netIncome = round($totalPrice - $serviceFee, 2);

        $from = $trip->startQuarter->name ?? '';
        $to = $trip->endQuarter->name ?? '';

        // Mijozdan yechish
        $clientBefore = $userBalance->balance;
        $userBalance->decrement('balance', $totalPrice);
        BalanceTransaction::create([
            'user_id' => $booking->user_id,
            'type' => 'debit',
            'amount' => $totalPrice,
            'balance_before' => $clientBefore,
            'balance_after' => $clientBefore - $totalPrice,
            'trip_id' => $trip->id,
            'reference_id' => $booking->id,
            'status' => 'success',
            'currency' => 'UZS',
            'reason' => "Posilka to'lovi ({$from} → {$to}), {$booking->weight} kg.",
        ]);

        // Driverga netto
        $driverBalance = UserBalance::where('user_id', $trip->driver_id)
            ->lockForUpdate()
            ->firstOrCreate(['user_id' => $trip->driver_id], ['balance' => 0]);
        $driverBefore = $driverBalance->balance;
        $driverBalance->increment('balance', $netIncome);
        BalanceTransaction::create([
            'user_id' => $trip->driver_id,
            'type' => 'credit',
            'amount' => $netIncome,
            'balance_before' => $driverBefore,
            'balance_after' => $driverBefore + $netIncome,
            'trip_id' => $trip->id,
            'reference_id' => $booking->id,
            'status' => 'success',
            'currency' => 'UZS',
            'reason' => "Posilka daromadi ({$from} → {$to}), {$booking->weight} kg. Xizmat haqi: {$serviceFee} UZS.",
        ]);

        // Kompaniyaga xizmat haqi
        $company = CompanyBalance::lockForUpdate()->first()
            ?: CompanyBalance::create(['balance' => 0, 'total_income' => 0]);
        $companyBefore = $company->balance;
        $company->increment('balance', $serviceFee);
        $company->increment('total_income', $serviceFee);
        CompanyBalanceTransaction::create([
            'company_balance_id' => $company->id,
            'amount' => $serviceFee,
            'balance_before' => $companyBefore,
            'balance_after' => $companyBefore + $serviceFee,
            'trip_id' => $trip->id,
            'type' => 'income',
            'reason' => "Posilka xizmat haqi ({$from} → {$to}).",
            'currency' => 'UZS',
        ]);
    }

    /**
     * Posilka bekor qilinganda to'lovni qaytarish (to'liq):
     * mijozga qaytarish, driverdan netto yechish, kompaniyadan xizmat haqi yechish.
     */
    protected function applyRefund(Trip $trip, ParcelBooking $booking): void
    {
        $totalPrice = (float) $booking->total_price;
        if ($totalPrice <= 0) {
            return;
        }

        $serviceFee = round($totalPrice * $this->serviceFeePercent() / 100, 2);
        $netIncome = round($totalPrice - $serviceFee, 2);

        $from = $trip->startQuarter->name ?? '';
        $to = $trip->endQuarter->name ?? '';

        // Mijozga qaytarish
        $userBalance = UserBalance::where('user_id', $booking->user_id)
            ->lockForUpdate()
            ->firstOrCreate(['user_id' => $booking->user_id], ['balance' => 0]);
        $clientBefore = $userBalance->balance;
        $userBalance->increment('balance', $totalPrice);
        BalanceTransaction::create([
            'user_id' => $booking->user_id,
            'type' => 'credit',
            'amount' => $totalPrice,
            'balance_before' => $clientBefore,
            'balance_after' => $clientBefore + $totalPrice,
            'trip_id' => $trip->id,
            'reference_id' => $booking->id,
            'status' => 'success',
            'currency' => 'UZS',
            'reason' => "Posilka bekor qilindi — qaytarish ({$from} → {$to}).",
        ]);

        // Driverdan netto yechish
        $driverBalance = UserBalance::where('user_id', $trip->driver_id)
            ->lockForUpdate()
            ->firstOrCreate(['user_id' => $trip->driver_id], ['balance' => 0]);
        $driverBefore = $driverBalance->balance;
        $driverBalance->decrement('balance', $netIncome);
        BalanceTransaction::create([
            'user_id' => $trip->driver_id,
            'type' => 'debit',
            'amount' => $netIncome,
            'balance_before' => $driverBefore,
            'balance_after' => $driverBefore - $netIncome,
            'trip_id' => $trip->id,
            'reference_id' => $booking->id,
            'status' => 'success',
            'currency' => 'UZS',
            'reason' => "Posilka bekor qilindi — daromad qaytarildi ({$from} → {$to}).",
        ]);

        // Kompaniyadan xizmat haqi yechish
        $company = CompanyBalance::lockForUpdate()->first();
        if ($company) {
            $companyBefore = $company->balance;
            $company->decrement('balance', $serviceFee);
            $company->decrement('total_income', $serviceFee);
            CompanyBalanceTransaction::create([
                'company_balance_id' => $company->id,
                'amount' => $serviceFee,
                'balance_before' => $companyBefore,
                'balance_after' => $companyBefore - $serviceFee,
                'trip_id' => $trip->id,
                'type' => 'outgoing',
                'reason' => "Posilka bekor qilindi — xizmat haqi qaytarildi ({$from} → {$to}).",
                'currency' => 'UZS',
            ]);
        }
    }

    protected function applyClientCancellationCharge(Trip $trip, ParcelBooking $booking): void
    {
        $total = (float) $booking->total_price;
        if ($total <= 0) {
            return;
        }

        // Booking paytidagi taqsimot: kompaniya haqi va driver netto
        $bookingServiceFee = round($total * $this->serviceFeePercent() / 100, 2);
        $driverNet         = round($total - $bookingServiceFee, 2);

        // Bekor qilish jarimasi (mijozdan) va driver kompensatsiyasi (foizlar .env dan)
        $cancelationFeePercent   = (float) config('services.fees.service_fee_for_canceling_order', 5);
        $driverCommissionPercent = (float) config('services.fees.service_fee_for_drivers_for_client_cancel_the_booking', 1);

        $cancelationFee   = round($total * $cancelationFeePercent / 100, 2);   // mijozdan ushlab qolinadi
        $refundForClient  = round($total - $cancelationFee, 2);                // mijozga qaytadi
        $driverCommission = round($total * $driverCommissionPercent / 100, 2); // driverga kompensatsiya
        $companyKept      = round($cancelationFee - $driverCommission, 2);     // kompaniya sof oladi

        $from = $trip->startQuarter->name ?? '';
        $to   = $trip->endQuarter->name ?? '';

        // === MIJOZGA QAYTARISH (total − cancelation fee) ===
        $userBalance = UserBalance::where('user_id', $booking->user_id)
            ->lockForUpdate()
            ->firstOrCreate(['user_id' => $booking->user_id], ['balance' => 0]);
        $clientBefore = $userBalance->balance;
        $userBalance->increment('balance', $refundForClient);
        BalanceTransaction::create([
            'user_id' => $booking->user_id,
            'type' => 'credit',
            'amount' => $refundForClient,
            'balance_before' => $clientBefore,
            'balance_after' => $clientBefore + $refundForClient,
            'trip_id' => $trip->id,
            'reference_id' => $booking->id,
            'status' => 'success',
            'currency' => 'UZS',
            'reason' => "Posilka bekor qilindi ({$from} → {$to}). Qaytarilgan: {$refundForClient} UZS, bekor qilish komissiyasi: {$cancelationFee} UZS.",
        ]);

        // === DRIVER: faqat NET yechiladi, kompensatsiya qo'shiladi ===
        $driverDelta = round($driverCommission - $driverNet, 2);
        $driverBalance = UserBalance::where('user_id', $trip->driver_id)
            ->lockForUpdate()
            ->firstOrCreate(['user_id' => $trip->driver_id], ['balance' => 0]);
        $driverBefore = $driverBalance->balance;
        if ($driverDelta >= 0) {
            $driverBalance->increment('balance', $driverDelta);
        } else {
            $driverBalance->decrement('balance', abs($driverDelta));
        }
        BalanceTransaction::create([
            'user_id' => $trip->driver_id,
            'type' => $driverDelta >= 0 ? 'credit' : 'debit',
            'amount' => abs($driverDelta),
            'balance_before' => $driverBefore,
            'balance_after' => $driverBefore + $driverDelta,
            'trip_id' => $trip->id,
            'reference_id' => $booking->id,
            'status' => 'success',
            'currency' => 'UZS',
            'reason' => "Posilka mijoz tomonidan bekor qilindi ({$from} → {$to}). Sizdan olingan NET: {$driverNet} UZS, kompensatsiya: {$driverCommission} UZS.",
        ]);

        // === KOMPANIYA: booking haqini qaytaradi, jarimani oladi, kompensatsiyani to'laydi ===
        $companyDelta = round($cancelationFee - $bookingServiceFee - $driverCommission, 2);
        $company = CompanyBalance::lockForUpdate()->first()
            ?: CompanyBalance::create(['balance' => 0, 'total_income' => 0]);
        $companyBefore = $company->balance;
        if ($companyDelta >= 0) {
            $company->increment('balance', $companyDelta);
            $company->increment('total_income', $companyDelta);
        } else {
            $company->decrement('balance', abs($companyDelta));
            $company->decrement('total_income', abs($companyDelta));
        }
        CompanyBalanceTransaction::create([
            'company_balance_id' => $company->id,
            'amount' => abs($companyDelta),
            'balance_before' => $companyBefore,
            'balance_after' => $companyBefore + $companyDelta,
            'trip_id' => $trip->id,
            'type' => $companyDelta >= 0 ? 'income' : 'outgoing',
            'reason' => "Posilka bekor qilindi ({$from} → {$to}). Kompaniya sof: {$companyKept} UZS (jarima {$cancelationFee} − driver kompensatsiyasi {$driverCommission}).",
            'currency' => 'UZS',
        ]);
    }

    /**
     * Mijozning o'z posilka so'rovlari.
     */
    public function getClientBookings()
    {
        $bookings = ParcelBooking::with('trip', 'type', 'user')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return $this->ok([
            'uz' => 'Posilka so‘rovlari olindi',
            'ru' => 'Запросы на посылки получены',
            'en' => 'Parcel requests fetched',
        ], ParcelBookingResource::collection($bookings));
    }

    public function getBookingById($id)
    {
        $booking = ParcelBooking::with('trip', 'type', 'user')
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$booking) {
            return $this->error([
                'uz' => 'Posilka so‘rovi topilmadi',
                'ru' => 'Запрос на посылку не найден',
                'en' => 'Parcel request not found',
            ], 404);
        }

        return $this->ok([
            'uz' => 'Posilka so‘rovi olindi',
            'ru' => 'Запрос на посылку получен',
            'en' => 'Parcel request fetched',
        ], new ParcelBookingResource($booking));
    }

    /**
     * Mijoz o'z posilkasining olib ketish (pickup) va topshirish (dropoff)
     * nuqtalarini yangilaydi. Faqat safar boshlanishidan oldin va posilka
     * hali faol (pending/confirmed) bo'lganda mumkin.
     */
    public function updateLocation($id, array $data)
    {
        $booking = ParcelBooking::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$booking) {
            return $this->error([
                'uz' => 'Posilka so‘rovi topilmadi',
                'ru' => 'Запрос на посылку не найден',
                'en' => 'Parcel request not found',
            ], 404);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return $this->error([
                'uz' => 'Bu posilka manzilini o‘zgartirib bo‘lmaydi',
                'ru' => 'Локацию этой посылки нельзя изменить',
                'en' => 'This parcel location cannot be changed',
            ], 422);
        }

        // Faqat safar boshlanishidan oldin
        $trip = $booking->trip;
        if ($trip && Carbon::now()->greaterThanOrEqualTo(Carbon::parse($trip->start_time))) {
            return $this->error([
                'uz' => 'Safar boshlangani uchun manzilni o‘zgartirib bo‘lmaydi',
                'ru' => 'Поездка началась, локацию нельзя изменить',
                'en' => 'The trip has already started, the location cannot be changed',
            ], 422);
        }

        $booking->update([
            'pickup_lat' => $data['pickup_lat'],
            'pickup_long' => $data['pickup_long'],
            'dropoff_lat' => $data['dropoff_lat'],
            'dropoff_long' => $data['dropoff_long'],
        ]);

        $booking->load('trip', 'type', 'user');

        return $this->ok([
            'uz' => 'Posilka manzili yangilandi',
            'ru' => 'Локация посылки обновлена',
            'en' => 'Parcel location updated',
        ], new ParcelBookingResource($booking));
    }

    /**
     * Mijoz o'z so'rovini bekor qiladi.
     */
    public function cancelByClient($id)
    {
        try {
            DB::beginTransaction();

            $booking = ParcelBooking::where('user_id', auth()->id())
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Posilka so‘rovi topilmadi',
                    'ru' => 'Запрос на посылку не найден',
                    'en' => 'Parcel request not found',
                ], 404);
            }

            if (!in_array($booking->status, ['pending', 'confirmed'])) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Bu so‘rovni bekor qilib bo‘lmaydi',
                    'ru' => 'Этот запрос нельзя отменить',
                    'en' => 'This request cannot be cancelled',
                ], 422);
            }

            // Safarni bloklab olamiz (sig'im/balans bilan ishlash uchun)
            $trip = Trip::with('startQuarter', 'endQuarter', 'parcel')
                ->where('id', $booking->trip_id)
                ->lockForUpdate()
                ->first();

            if (!$trip) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Safar topilmadi',
                    'ru' => 'Поездка не найдена',
                    'en' => 'Trip not found',
                ], 404);
            }

            // Faqat safar boshlanishidan oldin bekor qilish mumkin
            if (Carbon::now()->greaterThanOrEqualTo(Carbon::parse($trip->start_time))) {
                DB::rollBack();
                return $this->error([
                    'uz' => 'Safar boshlangani uchun posilkani bekor qilib bo‘lmaydi',
                    'ru' => 'Поездка уже началась, посылку нельзя отменить',
                    'en' => 'The trip has already started, the parcel cannot be cancelled',
                ], 422);
            }

            $wasConfirmed = $booking->status === 'confirmed';
            $booking->update(['status' => 'cancelled']);

            // Sig'imni tiklaymiz
            if ($trip->parcel) {
                $trip->parcel->increment('available_weight', (float) $booking->weight);
            }

            // Pul hisob-kitobi (trip/booking bekor qilish logikasi kabi):
            //  - mijozdan bekor qilish jarimasi ushlab qolinadi,
            //  - driverdan faqat NET (booking paytida olgan sof daromadi) yechiladi,
            //  - driverga kompensatsiya to'lanadi,
            //  - qolgan pul kompaniya hisobida qoladi.
            if ($wasConfirmed) {
                $this->applyClientCancellationCharge($trip, $booking);
            }

            DB::commit();

            $booking->load('trip.startQuarter', 'trip.endQuarter', 'type', 'user');

            // Haydovchiga xabar
            if ($booking->trip) {
                SendPushNotification::dispatch($booking->trip->driver_id, 'parcel.cancelled_by_client', [
                    'from' => $booking->trip->startQuarter->name ?? '',
                    'to' => $booking->trip->endQuarter->name ?? '',
                ], [
                    'trip_id' => (string) $booking->trip_id,
                    'parcel_booking_id' => (string) $booking->id,
                ]);
            }

            return $this->ok([
                'uz' => 'Posilka so‘rovi bekor qilindi',
                'ru' => 'Запрос на посылку отменён',
                'en' => 'Parcel request cancelled',
            ], new ParcelBookingResource($booking));
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin bitta posilkani bekor qiladi.
     * Ikki taraf ham zarar ko'rmaydi — hamma booking oldingi holatiga qaytadi:
     *  - Mijozga to'liq summa qaytariladi,
     *  - Haydovchidan faqat olgan netto daromadi yechiladi (jarima yo'q),
     *  - Kompaniyadan xizmat haqi yechiladi,
     *  - Safar sig'imi (available_weight) tiklanadi.
     * Web (admin) uchun mo'ljallangan — ['ok' => bool, 'message' => string] qaytaradi.
     */
    public function cancelByAdmin($id): array
    {
        try {
            DB::beginTransaction();

            $booking = ParcelBooking::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                DB::rollBack();
                return ['ok' => false, 'message' => 'Posilka topilmadi.'];
            }

            if (!in_array($booking->status, ['pending', 'confirmed'])) {
                DB::rollBack();
                return ['ok' => false, 'message' => 'Bu posilkani bekor qilib bo‘lmaydi (holat: ' . ucfirst($booking->status) . ').'];
            }

            // Safarni bloklab olamiz (sig'im/balans bilan ishlash uchun)
            $trip = Trip::with('startQuarter', 'endQuarter', 'parcel')
                ->where('id', $booking->trip_id)
                ->lockForUpdate()
                ->first();

            $wasConfirmed = $booking->status === 'confirmed';
            $booking->update(['status' => 'cancelled']);

            if ($trip) {
                // Sig'imni tiklaymiz
                if ($trip->parcel) {
                    $trip->parcel->increment('available_weight', (float) $booking->weight);
                }
                // To'lovni to'liq qaytaramiz (ikki taraf ham zarar ko'rmaydi)
                if ($wasConfirmed) {
                    $this->applyRefund($trip, $booking);
                }
            }

            DB::commit();

            // Mijoz (jo'natuvchi) va haydovchiga xabar
            if ($trip) {
                SendPushNotification::dispatch($booking->user_id, 'parcel.cancelled_by_admin', [
                    'from' => $trip->startQuarter->name ?? '',
                    'to' => $trip->endQuarter->name ?? '',
                ], [
                    'trip_id' => (string) $trip->id,
                    'parcel_booking_id' => (string) $booking->id,
                ]);

                SendPushNotification::dispatch($trip->driver_id, 'parcel.cancelled_by_admin_driver', [
                    'from' => $trip->startQuarter->name ?? '',
                    'to' => $trip->endQuarter->name ?? '',
                ], [
                    'trip_id' => (string) $trip->id,
                    'parcel_booking_id' => (string) $booking->id,
                ]);
            }

            return ['ok' => true, 'message' => 'Posilka bekor qilindi. Mijozga to‘liq qaytarildi, haydovchidan olgan daromadi yechildi — ikki taraf ham zarar ko‘rmadi.'];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['ok' => false, 'message' => 'Xatolik: ' . $e->getMessage()];
        }
    }

    // ============================= DRIVER =============================
    /**
     * Haydovchining barcha safarlariga kelgan posilka so'rovlari.
     */
    public function getDriverBookings()
    {
        $bookings = ParcelBooking::with('trip', 'type', 'user')
            ->whereHas('trip', fn ($q) => $q->where('driver_id', auth()->id()))
            ->latest()
            ->paginate(20);

        return $this->ok([
            'uz' => 'Posilka so‘rovlari olindi',
            'ru' => 'Запросы на посылки получены',
            'en' => 'Parcel requests fetched',
        ], ParcelBookingResource::collection($bookings));
    }

    /**
     * Bitta safar uchun posilka so'rovlari.
     */
    public function getDriverBookingsForTrip($tripId)
    {
        $trip = Trip::where('id', $tripId)->where('driver_id', auth()->id())->first();

        if (!$trip) {
            return $this->error([
                'uz' => 'Safar topilmadi',
                'ru' => 'Поездка не найдена',
                'en' => 'Trip not found',
            ], 404);
        }

        $bookings = ParcelBooking::with('trip', 'type', 'user')
            ->where('trip_id', $trip->id)
            ->latest()
            ->paginate(20);

        return $this->ok([
            'uz' => 'Posilka so‘rovlari olindi',
            'ru' => 'Запросы на посылки получены',
            'en' => 'Parcel requests fetched',
        ], ParcelBookingResource::collection($bookings));
    }
}
