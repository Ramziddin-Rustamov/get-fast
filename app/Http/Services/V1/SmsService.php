<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $token;

    public function __construct()
    {
        $this->authenticate();
    }

    private function authenticate()
    {
        $response = Http::post('https://notify.eskiz.uz/api/auth/login', [
            'email' => env('ESKIZ_EMAIL'),
            'password' => env('ESKIZ_PASSWORD'),
        ]);

        if ($response->successful()) {
            $this->token = $response->json()['data']['token'];
        }
    }

    public function sendSms($phone, $message)
    {
        if (!$this->token) {
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}"
        ])->post(env('ESKIZ_API_URL'), [
            'mobile_phone' => $phone,
            'message' => $message,
            'from' => '4546', // Eskizdan ruxsat olingan sender nomi
        ]);

        return $response->successful();
    }
}
