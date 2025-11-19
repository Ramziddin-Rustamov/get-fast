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



    public static function baseUrl(): string
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
        try {
            $token = self::getToken();
            if (!$token) {
                return ['error' => ['message' => 'Token olinmadi']];
            }

            $payload = [
                "jsonrpc" => "2.0",
                "method"  => "card.verify",
                "params"  => [[
                    "key" => $request->input('card_key'),
                    "confirm_code" => $request->input('confirm_code'),
                ]],
                "id" => (string) \Illuminate\Support\Str::uuid(),
            ];

            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
                ->post(self::baseUrl(), $payload);

            \App\Models\V1\PaymentLog::create([
                'request' => json_encode($payload),
                'response' => $response->body(),
            ]);

            if ($response->failed()) {
                return ['status' => 'error', 'message' => 'Verification failed' . $response->body()];
            }

            return $response->json();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Verification failed' . $e->getMessage()];
        }
    }

    // ### DONE ###################### --- DONE -------- #############################
    // public static function getCardInfo($request)
    // {
    //     try {
    //         $token = self::getToken();
    //         if (!$token) {
    //             return [
    //                 'status' => 'error',
    //                 'message' => 'Token olinmadi'
    //             ];
    //         }

    //         $payload = [
    //             "jsonrpc" => "2.0",
    //             "method"  => "card.info",
    //             "params"  => [[
    //                 "card_id" => $request->input('card_key'),
    //             ]],
    //             "id" => (string) Str::uuid(),
    //         ];

    //         $response = Http::withToken($token)
    //             ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
    //             ->post(self::baseUrl(), $payload);

    //         PaymentLog::create([
    //             'request' => json_encode($payload),
    //             'response' => $response->body(),
    //         ]);

    //         // Agar so‘rov muvaffaqiyatsiz bo‘lsa
    //         if ($response->failed()) {
    //             return [
    //                 'status' => 'error',
    //                 'message' => 'HTTP so‘rov bajarilmadi',
    //                 'error' => $response->json()
    //             ];
    //         }


    //         // Agar server error qaytarsa
    //         if (isset($json['error'])) {
    //             return [
    //                 'status' => 'error',
    //                 'message' => $response['error']['message'] ?? 'Noma’lum xatolik',
    //             ];
    //         }

    //         return [
    //             'status' => 'success',
    //             'message' => 'Card info',
    //             'data' => $response->json()
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'status' => 'error',
    //             'message' => 'Unexpected error: ' . $e->getMessage(),

    //         ];
    //     }
    // }

    //    DONE ###################### --- DONE -------- #############################
    public static function checkCardBalance($request)
    {

        try {
            $token = self::getToken();

            if (!$token) {
                return [
                    'status' => 'error',
                    'message' => 'Token olinmadi'
                ];
            }



            $payload = [
                "jsonrpc" => "2.0",
                "method"  => "card.check.balance",
                "params"  => [[
                    "card_id" => $request['card_key'],
                    "amount"  => (int) $request['amount'],
                ]],
                "id" => (string) Str::uuid(),
            ];

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
                ->post(self::baseUrl(), $payload);

            // // Log yozamiz
            // \App\Models\V1\PaymentLog::create([
            //     'request' => json_encode($payload),
            //     'response' => $response->body(),
            // ]);

            // Agar HTTP so‘rov xato bo‘lsa
            if ($response->failed()) {
                return [
                    'status' => 'error',
                    'message' => 'HTTP so‘rov bajarilmadi',
                    'error' => $response->json()
                ];
            }

            $json = $response->json();

            // Agar server xatolik yuborsa
            if (isset($json['error'])) {
                return [
                    'status' => 'error',
                    'message' => $json['error']['message'] ?? 'Noma’lum xatolik',
                ];
            }


            // Muvaffaqiyatli natija
            return $json['result']['response'];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Unexpected error: ' . $e->getMessage(),
            ];
        }
    }



    /** ------------------ 🟦 pay.create ------------------ */
    public static function payCreate($data)
    {
        try {
            $token = self::getToken();

            if (!$token) {
                return [
                    'status'  => false,
                    'message' => 'Token olinmadi'
                ];
            }

            // Build JSON-RPC request
            $payload = [
                "jsonrpc" => "2.0",
                "method"  => "pay.create",
                "params"  => [
                    $data
                ],
                "id"      => (string) Str::uuid(),
            ];

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
                ->post(self::baseUrl(), $payload);

            PaymentLog::create([
                'request'  => json_encode($payload),
                'response' => $response->body(),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage() . ' ' . BankErrorService::getMessage($e->getCode())
            ];
        }
    }



    /** ------------------ 🟩 pay.confirm ------------------ */
    public static function payConfirm(array $data)
    {

        try {
            $token = self::getToken();
            if (!$token) {
                return [
                    'status' => 'error',
                    'message' => 'Token is not found'
                ];
            }

            $payload = [
                "jsonrpc" => "2.0",
                "method"  => "pay.confirm",
                "params"  => [$data],
                "id" => (string) Str::uuid(),
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
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /** ------------------ 🟧 sms.resend ------------------ */
    public static function smsResend(string $payId)
    {
        try {
            $token = self::getToken();

            if (!$token) {
                return [
                    'status' => 'error',
                    'message' => 'There is no token available'
                ];
            }

            $payload = [
                "jsonrpc" => "2.0",
                "method"  => "sms.resend",
                "params"  => [[
                    "pay_id" => $payId,
                ]],
                "id" => (string) Str::uuid(),
            ];

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(self::baseUrl(), $payload);

            // Log yozamiz
            PaymentLog::create([
                'request'  => json_encode($payload),
                'response' => $response->body(),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage() . '' . BankErrorService::getMessage($e->getCode())
            ];
        }
    }


    /** ------------------ 🟦 pay.get ------------------ */
    public static function payGet(string $payId)
    {
        try {
            $token = self::getToken();
            if (!$token) {
                return [
                    'status' => 'error',
                    'message' => 'Token olinmadi'
                ];
            }

            $payload = [
                "jsonrpc" => "2.0",
                "method"  => "pay.get",
                "params"  => [[
                    "pay_id" => $payId
                ]],
                "id" => (string) Str::uuid(),
            ];

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(self::baseUrl(), $payload);

            // Log
            PaymentLog::create([
                'request'  => json_encode($payload),
                'response' => $response->body(),
            ]);

            return $response->json();
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
