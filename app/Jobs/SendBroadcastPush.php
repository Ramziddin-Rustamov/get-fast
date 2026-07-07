<?php

namespace App\Jobs;

use App\Models\BroadcastMessage;
use App\Models\User;
use App\Services\V1\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendBroadcastPush implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $broadcastId)
    {
    }

    public function handle(FcmService $fcm): void
    {
        $broadcast = BroadcastMessage::find($this->broadcastId);

        if (! $broadcast) {
            return;
        }

        $broadcast->update(['status' => 'sending']);

        // Auditoriya bo'yicha device tokeni bor foydalanuvchilar (til bilan).
        $query = User::query()
            ->whereNotNull('device_token')
            ->with('authLanguage:id,user_id,language');

        if (in_array($broadcast->audience, ['driver', 'client'], true)) {
            $query->where('role', $broadcast->audience);
        }

        $recipients = 0;
        $sent       = 0;

        $query->select('id', 'device_token')
            ->chunkById(500, function ($users) use ($broadcast, $fcm, &$recipients, &$sent) {
                foreach ($users as $user) {
                    $recipients++;

                    // Foydalanuvchi tili (uz/ru/en), yo'q bo'lsa uz
                    $lang    = $user->authLanguage->language ?? BroadcastMessage::FALLBACK_LANG;
                    $content = $broadcast->forLang($lang);

                    try {
                        if ($fcm->sendToToken(
                            $user->device_token,
                            $content['title'] ?: config('app.name'),
                            $content['body'],
                            ['broadcast_id' => $broadcast->id, 'type' => 'broadcast']
                        )) {
                            $sent++;
                        }
                    } catch (Throwable $e) {
                        Log::error('Broadcast push xatosi', [
                            'user_id' => $user->id,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            });

        $broadcast->update([
            'status'           => $sent > 0 ? 'sent' : 'failed',
            'recipients_count' => $recipients,
            'sent_count'       => $sent,
        ]);
    }

    public function failed(Throwable $e): void
    {
        BroadcastMessage::where('id', $this->broadcastId)->update(['status' => 'failed']);
    }
}
