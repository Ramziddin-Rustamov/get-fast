<?php

namespace App\Http\Controllers\Api\V1;



use App\Http\Controllers\Controller;
use App\Models\V1\PaymentCard;
use App\Models\V1\Payment;
use App\Models\V1\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HamkorBankController extends Controller
{
    protected $apiUrl = 'https://host/acquiring/v1';
    protected $token;

    public function __construct()
    {
        $this->token = $this->getAccessToken();
    }

    private function getAccessToken()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(env('BANK_CLIENT_ID') . ':' . env('BANK_CLIENT_SECRET'))
        ])->post('https://host/token', [
            'grant_type' => 'client_credentials'
        ]);

        return $response['access_token'] ?? null;
    }

    private function sendRequest(string $method, array $params)
    {
        $requestId = (string) Str::uuid();

        // So‘rov logga yoziladi
        PaymentLog::create([
            'method' => $method,
            'request_data' => $params,
        ]);

        $response = Http::withToken($this->token)
            ->post($this->apiUrl, [
                'jsonrpc' => '2.0',
                'method' => $method,
                'params' => [$params],
                'id' => $requestId,
            ]);

        // Javobni logga yozish
        PaymentLog::where('method', $method)
            ->whereNull('response_data')
            ->latest()
            ->first()
            ?->update(['response_data' => $response->json()]);

        return $response->json();
    }

    public function listCards(Request $request)
    {
        return $this->sendRequest('card.list', ['phone' => $request->phone]);
    }

    public function addCard(Request $request)
    {
        $user = Auth::user();

        // Bankka yuborish
        $response = $this->sendRequest('card.create', [
            'number' => $request->number,
            'expiry' => $request->expiry,
            'phone' => $request->phone,
        ]);

        if (isset($response['result']['card_id'])) {
            // Barcha kartalarni nofaol qilish
            $user->cards()->update(['is_active' => false]);

            // Yangi kartani bazaga yozish
            PaymentCard::create([
                'user_id' => $user->id,
                'card_id' => $response['result']['card_id'],
                'masked_pan' => $response['result']['masked_pan'] ?? '****',
                'expiry' => $request->expiry,
                'is_verified' => false,
                'is_active' => true,
            ]);
        }

        return $response;
    }

    public function verifyCard(Request $request)
    {
        $response = $this->sendRequest('card.verify', [
            'number' => $request->number,
            'expiry' => $request->expiry,
            'key' => $request->key,
            'confirm_code' => $request->confirm_code,
        ]);

        if (isset($response['result']['card_id'])) {
            PaymentCard::where('card_id', $response['result']['card_id'])
                ->update(['is_verified' => true]);
        }

        return $response;
    }

    public function cardInfo(Request $request)
    {
        return $this->sendRequest('card.info', ['card_id' => $request->card_id]);
    }

    public function checkBalance(Request $request)
    {
        return $this->sendRequest('card.check.balance', ['card_id' => $request->card_id]);
    }

    public function createPayment(Request $request)
    {
        $response = $this->sendRequest('pay.create', $request->all());

        if (isset($response['result']['pay_id'])) {
            Payment::create([
                'pay_id' => $response['result']['pay_id'],
                'external_id' => $request->external_id ?? null,
                'card_id' => $request->card['id'],
                'amount' => $request->amount,
                'currency_code' => $request->currency_code,
                'status' => 'created',
                'payer_data' => $request->payer_data ?? [],
                'receiver_data' => $request->receiver_data ?? [],
            ]);
        }

        return $response;
    }

    public function confirmPayment(Request $request)
    {
        $response = $this->sendRequest('pay.confirm', $request->all());

        if (isset($response['result']['pay_id'])) {
            Payment::where('pay_id', $response['result']['pay_id'])
                ->update(['status' => 'confirmed']);
        }

        return $response;
    }

    public function cancelPayment(Request $request)
    {
        $response = $this->sendRequest('pay.cancel', ['pay_id' => $request->pay_id]);

        if (isset($request->pay_id)) {
            Payment::where('pay_id', $request->pay_id)
                ->update(['status' => 'canceled']);
        }

        return $response;
    }

    public function partialRefund(Request $request)
    {
        return $this->sendRequest('pay.cancelPartial', $request->all());
    }

    public function getPayment(Request $request)
    {
        return $this->sendRequest('pay.get', ['pay_id' => $request->pay_id]);
    }

    public function getPaymentByExternalId(Request $request)
    {
        return $this->sendRequest('pay.get.ext', ['ext_id' => $request->ext_id]);
    }
}



?>

