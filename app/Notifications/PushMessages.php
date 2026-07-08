<?php

namespace App\Notifications;

/**
 * Barcha push xabar shablonlari (uz/ru/en) bitta joyda.
 *
 * Yangi hodisa qo'shish uchun shu yerga event kaliti bilan
 * uz/ru/en 'title' va 'body' qo'shing. Body ichida {kalit}
 * ko'rinishidagi joylar params bilan almashtiriladi.
 */
class PushMessages
{
    public const FALLBACK_LANG = 'uz';

    public const TEMPLATES = [

        // ============ POCHTA (PARCEL) ============

        // Mijoz posilka yubordi → haydovchiga
        'parcel.new' => [
            'uz' => ['title' => 'Yangi posilka',       'body' => 'Safaringizga posilka qabul qilindi ({from} → {to}), {weight} kg.'],
            'ru' => ['title' => 'Новая посылка',        'body' => 'На вашу поездку принята посылка ({from} → {to}), {weight} кг.'],
            'en' => ['title' => 'New parcel',           'body' => 'A parcel was accepted for your trip ({from} → {to}), {weight} kg.'],
        ],

        // Mijoz posilkani bekor qildi → haydovchiga
        'parcel.cancelled_by_client' => [
            'uz' => ['title' => 'Posilka bekor qilindi', 'body' => 'Mijoz posilkasini bekor qildi ({from} → {to}).'],
            'ru' => ['title' => 'Посылка отменена',      'body' => 'Клиент отменил посылку ({from} → {to}).'],
            'en' => ['title' => 'Parcel cancelled',      'body' => 'The client cancelled their parcel ({from} → {to}).'],
        ],

        // Haydovchi/admin pochta qabulini o'chirdi → posilka egasiga (mijozga)
        'parcel.disabled' => [
            'uz' => ['title' => 'Posilka bekor qilindi', 'body' => 'Safar pochta qabulini to‘xtatgani sababli posilkangiz bekor qilindi ({from} → {to}).'],
            'ru' => ['title' => 'Посылка отменена',      'body' => 'Ваша посылка отменена, так как поездка перестала принимать посылки ({from} → {to}).'],
            'en' => ['title' => 'Parcel cancelled',      'body' => 'Your parcel was cancelled because the trip stopped accepting parcels ({from} → {to}).'],
        ],

        // ============ YO'LOVCHI (BOOKING) ============

        // Mijoz safarni band qildi → haydovchiga
        'booking.new' => [
            'uz' => ['title' => 'Yangi buyurtma',        'body' => 'Safaringizga yangi buyurtma tushdi ({from} → {to}), {seats} o‘rin.'],
            'ru' => ['title' => 'Новый заказ',           'body' => 'На вашу поездку поступил новый заказ ({from} → {to}), мест: {seats}.'],
            'en' => ['title' => 'New booking',           'body' => 'You received a new booking for your trip ({from} → {to}), {seats} seat(s).'],
        ],

        // Mijoz mavjud buyurtmaga yo'lovchi qo'shdi → haydovchiga
        'booking.passenger_added' => [
            'uz' => ['title' => 'Yangi yo‘lovchi',       'body' => 'Buyurtmaga yangi yo‘lovchi qo‘shildi ({from} → {to}).'],
            'ru' => ['title' => 'Новый пассажир',        'body' => 'К заказу добавлен новый пассажир ({from} → {to}).'],
            'en' => ['title' => 'New passenger',         'body' => 'A new passenger was added to the booking ({from} → {to}).'],
        ],

        // Mijoz buyurtmani bekor qildi → haydovchiga
        'booking.cancelled_by_client' => [
            'uz' => ['title' => 'Buyurtma bekor qilindi', 'body' => 'Mijoz buyurtmani bekor qildi ({from} → {to}).'],
            'ru' => ['title' => 'Заказ отменён',          'body' => 'Клиент отменил заказ ({from} → {to}).'],
            'en' => ['title' => 'Booking cancelled',      'body' => 'The client cancelled the booking ({from} → {to}).'],
        ],

        // Mijoz yo'lovchini bekor qildi → haydovchiga
        'booking.passenger_removed' => [
            'uz' => ['title' => 'Yo‘lovchi bekor qilindi', 'body' => 'Mijoz buyurtmadan bir yo‘lovchini bekor qildi ({from} → {to}).'],
            'ru' => ['title' => 'Пассажир отменён',        'body' => 'Клиент отменил одного пассажира из заказа ({from} → {to}).'],
            'en' => ['title' => 'Passenger removed',       'body' => 'The client removed a passenger from the booking ({from} → {to}).'],
        ],

        // Admin safarni bekor qildi → mijozga
        'trip.cancelled_by_admin' => [
            'uz' => ['title' => 'Safar bekor qilindi',   'body' => 'Safaringiz admin tomonidan bekor qilindi ({from} → {to}). To‘lov qaytariladi.'],
            'ru' => ['title' => 'Поездка отменена',       'body' => 'Ваша поездка отменена администратором ({from} → {to}). Средства будут возвращены.'],
            'en' => ['title' => 'Trip cancelled',         'body' => 'Your trip was cancelled by an administrator ({from} → {to}). A refund will be issued.'],
        ],

        // Admin yo'lovchini bekor qildi → mijozga
        'booking.passenger_cancelled_by_admin' => [
            'uz' => ['title' => 'Yo‘lovchi bekor qilindi', 'body' => 'Buyurtmangizdan bir yo‘lovchi admin tomonidan bekor qilindi ({from} → {to}).'],
            'ru' => ['title' => 'Пассажир отменён',        'body' => 'Один пассажир из вашего заказа отменён администратором ({from} → {to}).'],
            'en' => ['title' => 'Passenger cancelled',     'body' => 'A passenger from your booking was cancelled by an administrator ({from} → {to}).'],
        ],
    ];

    /**
     * Event + til + parametrlar → ['title' => ..., 'body' => ...]
     */
    public static function resolve(string $event, string $lang, array $params = []): array
    {
        $template = self::TEMPLATES[$event] ?? null;

        if (!$template) {
            return ['title' => config('app.name'), 'body' => ''];
        }

        $content = $template[$lang] ?? $template[self::FALLBACK_LANG];

        return [
            'title' => self::fill($content['title'], $params),
            'body'  => self::fill($content['body'], $params),
        ];
    }

    protected static function fill(string $text, array $params): string
    {
        foreach ($params as $key => $value) {
            $text = str_replace('{' . $key . '}', (string) $value, $text);
        }
        return $text;
    }
}
