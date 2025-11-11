<?php

namespace App\Services\V1;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AcquiringClient
{
    /**
     * Asosiy JSON-RPC so'rov yuboruvchi funksiya
     */
    public static function jsonRpc(string $method, array $params)
    {
        $payload = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => [$params],
            'id' => (string) Str::uuid(),
        ];

        $ch = curl_init(env('ACQUIRING_URL'));

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('ACQUIRING_TOKEN'),
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'error' => [
                    'message' => "Curl error: $error"
                ]
            ];
        }

        curl_close($ch);
        $decoded = json_decode($result, true);

        // Foydali debug maqsadida API javobini logga yozish (agar kerak bo‘lsa)
        if (config('app.debug')) {
           Log::info('Acquiring API Request', [
                'method' => $method,
                'payload' => $payload,
                'response' => $decoded,
            ]);
        }

        return $decoded;
    }
}
