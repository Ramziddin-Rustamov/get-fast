<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\PushMessages;
use App\Services\V1\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Universal push xabar jo'natish jobi.
 *
 * Ishlatish:
 *   SendPushNotification::dispatch($userId, 'parcel.new', [
 *       'from' => 'Buxoro', 'to' => 'Jizzax', 'weight' => 3.5,
 *   ], ['trip_id' => 5]);
 *
 * Foydalanuvchi tili authLanguage'dan olinadi (uz/ru/en), xabar
 * matni PushMessages registridan mos tilda tayyorlanadi.
 */
class SendPushNotification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  int    $userId  Kimga yuboriladi
     * @param  string $event   PushMessages event kaliti
     * @param  array  $params  Shablon o'rinlarini to'ldirish uchun
     * @param  array  $data    FCM data payload (frontend navigatsiya uchun)
     */
    public function __construct(
        public int $userId,
        public string $event,
        public array $params = [],
        public array $data = [],
    ) {}

    public function handle(FcmService $fcm): void
    {
        $user = User::with('authLanguage:id,user_id,language')
            ->select('id', 'device_token')
            ->find($this->userId);

        if (!$user || !$user->device_token) {
            return;
        }

        $lang = $user->authLanguage->language ?? PushMessages::FALLBACK_LANG;
        $content = PushMessages::resolve($this->event, $lang, $this->params);

        if ($content['body'] === '') {
            return;
        }

        try {
            $fcm->sendToToken(
                $user->device_token,
                $content['title'],
                $content['body'],
                $this->data + ['event' => $this->event],
            );
        } catch (Throwable $e) {
            Log::error('Push xatosi', [
                'user_id' => $this->userId,
                'event' => $this->event,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
