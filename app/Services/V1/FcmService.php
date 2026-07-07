<?php

namespace App\Services\V1;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Firebase Cloud Messaging (HTTP v1 API).
 *
 * Qo'shimcha composer paketsiz ishlaydi: service account JSON dan
 * OAuth2 access token oladi (RS256 bilan imzolangan JWT orqali) va
 * FCM v1 endpointiga push yuboradi.
 */
class FcmService
{
    private const SCOPE      = 'https://www.googleapis.com/auth/firebase.messaging';
    private const TOKEN_URI  = 'https://oauth2.googleapis.com/token';

    /**
     * Bitta device tokenga push yuboradi.
     *
     * @return bool muvaffaqiyatli bo'lsa true
     */
    public function sendToToken(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        $credentials = $this->credentials();
        $projectId   = config('services.fcm.project_id') ?: ($credentials['project_id'] ?? null);

        if (! $projectId) {
            throw new RuntimeException('FCM project_id sozlanmagan (.env: FCM_PROJECT_ID).');
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // FCM data qiymatlari faqat string bo'lishi kerak.
        $stringData = array_map(fn ($v) => (string) $v, $data);

        $response = Http::withToken($this->accessToken($credentials))
            ->acceptJson()
            ->post($url, [
                'message' => [
                    'token'        => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data'    => $stringData,
                    'android' => ['priority' => 'high'],
                    'apns'    => [
                        'headers' => ['apns-priority' => '10'],
                        'payload' => ['aps' => ['sound' => 'default']],
                    ],
                ],
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::warning('FCM yuborishda xatolik', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        return false;
    }

    /**
     * Service account uchun OAuth2 access token (55 daqiqa keshlanadi).
     */
    private function accessToken(array $credentials): string
    {
        return Cache::remember('fcm_access_token', 3300, function () use ($credentials) {
            $now = time();

            $jwt = $this->encodeJwt(
                ['alg' => 'RS256', 'typ' => 'JWT'],
                [
                    'iss'   => $credentials['client_email'],
                    'scope' => self::SCOPE,
                    'aud'   => self::TOKEN_URI,
                    'iat'   => $now,
                    'exp'   => $now + 3600,
                ],
                $credentials['private_key']
            );

            $response = Http::asForm()->post(self::TOKEN_URI, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (! $response->successful() || ! $response->json('access_token')) {
                throw new RuntimeException('FCM access token olinmadi: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * RS256 bilan imzolangan JWT hosil qiladi.
     */
    private function encodeJwt(array $header, array $payload, string $privateKey): string
    {
        $segments = [
            $this->base64Url(json_encode($header)),
            $this->base64Url(json_encode($payload)),
        ];

        $signingInput = implode('.', $segments);
        $signature    = '';

        if (! openssl_sign($signingInput, $signature, $privateKey, 'sha256WithRSAEncryption')) {
            throw new RuntimeException('FCM JWT imzolab bo\'lmadi. private_key ni tekshiring.');
        }

        $segments[] = $this->base64Url($signature);

        return implode('.', $segments);
    }

    private function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Service account JSON faylini o'qiydi.
     */
    private function credentials(): array
    {
        $path = config('services.fcm.credentials');

        if (! $path || ! is_file($path)) {
            throw new RuntimeException(
                'FCM service account fayli topilmadi. .env dagi FCM_CREDENTIALS_PATH ni tekshiring.'
            );
        }

        $json = json_decode(file_get_contents($path), true);

        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            throw new RuntimeException('FCM service account JSON noto\'g\'ri formatda.');
        }

        return $json;
    }
}
