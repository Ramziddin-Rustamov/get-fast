<?php

namespace App\Services\V1;

use App\Models\V1\Card;
use App\Models\V1\PaymentLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class HamkorbankService
{
    protected static function baseUrl(): string
    {
        return rtrim(config('services.hamkorbank.url'), '/');
    }

    public static function getToken(): ?string
    {
        $url = 'https://dev-open-api.hamkorbank.uz/token';
        $key = config('services.hamkorbank.key');
        $secret = config('services.hamkorbank.secret');

        $response = Http::withBasicAuth($key, $secret)
            ->asForm()
            ->post($url, ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            PaymentLog::create([
                'request' => 'token_request',
                'response' => $response->body(),
            ]);
            return null;
        }

        return $response->json()['access_token'] ?? null;
    }

    /** ✅ 1. Foydalanuvchining telefon raqamiga tegishli kartalar ro‘yxatini olish */
    //DONE ###################### --- DONE -------- #############################
    public static function cardListForPhoneNumber($phoneNumber)
    {
        if (!$phoneNumber) {
            return response()->json(['error' => 'Telefon raqam kiritilmadi'], 422);
        }
        $token = self::getToken();
        if (!$token) {
            return response()->json(['error' => 'Token olinmadi'], 500);
        }

        $payload = [
            "jsonrpc" => "2.0",
            "method"  => "card.list",
            "params"  => [[
                "phone" => $phoneNumber,
            ]],
            "id" => (string) Str::uuid(),
        ];

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post(self::baseUrl(), $payload);

        return response()->json([
            'status' => $response->status(),
            'data' => $response->json(),
        ]);
    }

    /** ✅ 2. Karta qo‘shish */
    //###################### --- DONE -------- #############################
    public static function addCard(Request $request)
    {
        $token = self::getToken();
        if (!$token) {
            return [
                'status' => false,
                'error' => 'Token olinmadi'
            ];
        }
        $payload = [
            "jsonrpc" => "2.0",
            "method"  => "card.create",
            "params"  => [[
                "number" => $request->input('number'),
                "expiry" => $request->input('expiry'),
                "phone"  => $request->input('phone'),
            ]],
            "id" => (string) Str::uuid(),
        ];


        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post(self::baseUrl(), $payload);

        PaymentLog::create([
            'request' => json_encode($payload),
            'user_id' => Auth::id(),
            'response' => $response->body(),
        ]);

        if ($response->failed()) {
            return [
                $response->json(),
                'status' => 'error',
            ];
        }

        return $response->json();
    }

    /** ✅ 3. Karta verify qilish (SMS kodi bilan) */
    //DONE ###################### --- DONE -------- #############################

    public static function verifyCard($request)
    {
        $token = self::getToken();
        if (!$token) {
            return response()->json(['error' => 'Token olinmadi'], 500);
        }

        $payload = [
            "jsonrpc" => "2.0",
            "method"  => "card.verify",
            "params"  => [[
                "key" => $request->input('key'),
                "confirm_code" => $request->input('confirm_code'),
            ]],
            "id" => (string) Str::uuid(),
        ];

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
            ->post(self::baseUrl(), $payload);

        PaymentLog::create([
            'request' => json_encode($payload),
            'response' => $response->body(),
        ]);

        if ($response->failed()) {
            return response()->json(['status' => false, 'error' => $response->json()], $response->status());
        }

        $json = $response->json();
        $result = $json['result'] ?? [];

        if (isset($result['id']) && Auth::check()) {
            Card::updateOrCreate(
                ['card_id' => $result['id']],
                [
                    'user_id' => Auth::id(),
                    'number' => $result['number'] ?? null,
                    'expiry' => $result['expiry'] ?? null,
                    'is_default' => !Card::where('user_id', Auth::id())->exists(),
                    'status' => 'verified',
                ]
            );
        }

        return response()->json($json);
    }

    public static function payCreate(array $data)
    {
        $token = self::getToken();
        if (!$token) {
            return ['error' => 'Token olinmadi'];
        }

        $card = [];
        if (!empty($data['card_id'])) {
            $card['id'] = $data['card_id'];
        } else {
            $card['number'] = $data['card_number'];
            $card['expiry'] = $data['card_expiry'];
        }

        $payload = [
            "jsonrpc" => "2.0",
            "method" => "pay.create",
            "params" => [[
                "external_id" => $data['external_id'],
                "amount" => (int)$data['amount'],
                "currency_code" => $data['currency_code'],
                "card" => $card,
            ]],
            "id" => (string) Str::uuid(),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json; charset=utf-8',
        ])->post(self::baseUrl(), $payload);

        PaymentLog::create([
            'endpoint' => 'pay.create',
            'request' => json_encode($payload),
            'response' => $response->body(),
            'status' => $response->status(),
        ]);

        return $response->json();
    }

    public static function payConfirm(array $data)
    {
        $token = self::getToken();
        if (!$token) {
            return ['error' => 'Token olinmadi'];
        }

        $params = ['pay_id' => $data['pay_id']];
        if (!empty($data['confirm_code'])) {
            $params['confirm_code'] = $data['confirm_code'];
        }
        if (isset($data['hold'])) {
            $params['hold'] = (bool)$data['hold'];
        }

        $payload = [
            "jsonrpc" => "2.0",
            "method" => "pay.confirm",
            "params" => [$params],
            "id" => (string) Str::uuid(),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json; charset=utf-8',
        ])->post(self::baseUrl(), $payload);

        PaymentLog::create([
            'endpoint' => 'pay.confirm',
            'request' => json_encode($payload),
            'response' => $response->body(),
            'status' => $response->status(),
        ]);

        return $response->json();
    }




    /** ✅ 1. Karta balansi */
    public static function checkCardBalanceIsAvailable($request)
    {
        $token = self::getToken();
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token olinmadi'
            ], 500);
        }

        $payload = [
            "jsonrpc" => "2.0",
            "method"  => "card.info",
            "params"  => [[
                "card_id" => $request->card_id,
                "amount" => $request->amount
            ]],
            "id" => (string) Str::uuid()
        ];


        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post(self::baseUrl(), $payload);

        PaymentLog::create([
            'request' => json_encode($payload),
            'response' => $response->body(),
        ]);

        return $response->json();

        if ($response->failed()) {
            return response()->json([
                'status' => false,
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json([
            'status' => true,
            'data' => $response->json()['result'] ?? null
        ]);
    }


    public static function checkBalanceByCardId($request)
    {
        try {
            $token = self::getToken();
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token olinmadi'
                ], 500);
            }

            $payload = [
                "jsonrpc" => "2.0",
                "method"  => "card.info",
                "params"  => [[
                    "card_id" => $request['card_id'],
                ]],
                "id" => (string) Str::uuid()
            ];

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(self::baseUrl(), $payload);

            PaymentLog::create([
                'request' => json_encode($payload),
                'response' => $response->body(),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
