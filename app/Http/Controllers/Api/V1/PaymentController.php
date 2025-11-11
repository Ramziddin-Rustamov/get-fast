<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Models\v1\PaymentLog;
use App\Services\V1\HamkorbankService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class PaymentController extends Controller
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

   
}
