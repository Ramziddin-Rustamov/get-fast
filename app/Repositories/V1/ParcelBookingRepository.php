<?php

namespace App\Repositories\V1;

use App\Models\V1\Trip;
use App\Models\V1\ParcelBooking;
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

            // Og'irlik chegarasi
            if (!is_null($parcel->max_weight) && $data['weight'] > $parcel->max_weight) {
                DB::rollBack();
                return $this->error([
                    'uz' => "Og‘irlik chegaradan oshib ketdi (maksimal {$parcel->max_weight} kg)",
                    'ru' => "Вес превышает лимит (максимум {$parcel->max_weight} кг)",
                    'en' => "Weight exceeds the limit (max {$parcel->max_weight} kg)",
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

            $booking = ParcelBooking::create([
                'parcel_id' => $parcel->id,
                'trip_id' => $trip->id,
                'parcel_type_id' => $data['parcel_type_id'],
                'user_id' => auth()->id(),
                'receiver_phone' => $data['receiver_phone'],
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

            $booking->update(['status' => 'cancelled']);

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
