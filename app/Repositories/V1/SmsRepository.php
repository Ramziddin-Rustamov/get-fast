<?php

namespace App\Repositories\V1;

use App\Models\Sms;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $this->smsUrl = config('services.sms.url', env('SMS_API_URL'));
        $this->smsFaceName = config('services.sms.face_name', env('SMS_API_FACE_NAME'));
        $this->smsUsername = config('services.sms.username', env('SMS_API_USERNAME'));
        $this->smsPassword = config('services.sms.password', env('SMS_API_PASSWORD'));
    }

    public function send(string $phone, string $message, string $action)
    {
        try {
            $messageId = $this->getMessageID($action, $phone, $message);

            $message = [
                'messages' => [
                    [
                        'recipient' => $phone,
                        'message-id' => $messageId,
                        'sms' => [
                            'originator' => "$this->smsFaceName",
                            'content' => [
                                'text' => $message,
                            ],
                        ],
                    ],
                ],
            ];

            Http::withBasicAuth($this->smsUsername, $this->smsPassword)->post($this->smsUrl, $message);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('SMS sending failed: ' . $exception->getMessage());
            return response()->json(['error' => 'Server error occurred'], 500);
        }
    }

    protected function getMessageID($action, $phone, $message): string
    {
        try {
            $model = new Sms();
            $model->action = $action;
            $model->phone = $phone;
            $model->content = $message;
            $model->save();

            $message_id = $model->action . '_' . $model->id;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('SMS messageID generate failed: ' . $exception->getMessage());
            return response()->json(['error' => 'Server error occurred'], 500);
        }

        return $message_id;
    }
}
