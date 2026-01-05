<?php

namespace App\Repositories\V1;

use App\Models\Sms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsRepository
{
    protected string $smsUrl;
    protected string $smsFaceName;
    protected string $smsUsername;
    protected string $smsPassword;

    public function __construct()
    {
        $this->smsUrl = config('services.sms.url');
        $this->smsFaceName = config('services.sms.face_name');
        $this->smsUsername = config('services.sms.username');
        $this->smsPassword = config('services.sms.password');
    }

    /**
     * Send SMS (used in Queue)
     */
    public function send(string $phone, string $text, string $action): void
    {
        $phone = $this->normalizePhone($phone);
        $messageId = $this->getMessageID($action, $phone, $text);

        $payload = [
            'messages' => [
                [
                    'recipient' => $phone,
                    'message-id' => $messageId,
                    'sms' => [
                        'originator' => $this->smsFaceName,
                        'content' => [
                            'text' => $text,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withBasicAuth(
                $this->smsUsername,
                $this->smsPassword
            )->post($this->smsUrl, $payload);

            if (!$response->successful()) {
                Log::error('SMS SEND FAILED', [
                    'phone'  => $phone,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                throw new \Exception('SMS API error: ' . $response->status());
            }

            Log::info('SMS SENT SUCCESS', [
                'phone' => $phone,
                'message_id' => $messageId,
            ]);
        } catch (\Throwable $e) {
            Log::critical('SMS QUEUE FAILED', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function getMessageID(string $action, string $phone, string $message): string
    {
        $sms = new Sms();
        $sms->action = $action;
        $sms->phone = $phone;
        $sms->content = $message;
        $sms->save();

        return $action . '_' . $sms->id;
    }

    /**
     * +998901234567 → 998901234567
     */
    protected function normalizePhone(string $phone): string
    {
        return ltrim($phone, '+');
    }
}
