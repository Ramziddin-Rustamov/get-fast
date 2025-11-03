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



    /**
     * 💰 Karta orqali to‘lov (pay.create)
     */
    public function chargeCard(Request $request)
    {

        $auth_id =  Auth::user()->id;
        $validated = $request->validate([
            'external_id' => 'required|string',
            'amount' => 'required|numeric',
            'currency_code' => 'required|string',
            'user_card_id' => 'required|exists:cards,id',
        ]);
        $card = Card::where('user_id', $auth_id)->where('id', $validated['user_card_id'])->first();

        $response = HamkorbankService::payCreate($validated);

        return response()->json([
            'status' => $response['error'] ?? false ? false : true,
            'data' => $response,
        ]);
    }

    /**
     * 📩 To‘lovni SMS orqali tasdiqlash (pay.confirm)
     */
    public function confirmPayment(Request $request)
    {
        $validated = $request->validate([
            'pay_id' => 'required|string',
            'confirm_code' => 'nullable|string|min:4|max:7',
            'hold' => 'nullable|boolean',
        ]);

        $response = HamkorbankService::payConfirm($validated);

        return response()->json([
            'status' => $response['error'] ?? false ? false : true,
            'data' => $response,
        ]);
    }
   
}
