<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Services\V1\BankErrorService;
use App\Services\V1\HamkorbankService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{


    public function createPayment(Request $request)
    {
        $user = auth()->user(); // kim to'layotganini olish

        // 1. Faqat amount validatsiya
        $data = $request->validate([
            'amount' => 'required|numeric|min:1000'
        ]);

        // 2. Userning default kartasini olish
        $card = Card::where('user_id', $user->id)
            ->where('is_default', 1)->where('status', 'verified')
            ->first();

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Default karta topilmadi'
            ], 400);
        }

        // 2.1 Userning default kartasini olish
        if ($user->isDefaultCard->card_id == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'User has no default card'
            ]);
        }

        // 3. Check balance: karta parametrlari + amount retur 0 or 1 
        $check = HamkorbankService::checkCardBalance([
            'card_key'    => $user->isDefaultCard->card_id,
            'amount'      => $data['amount'],
        ]);


        if ($check == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Karta balansida mablag‘ yetarli emas'
            ], 400);
        }

        // 4. Payment yaratish uchun API-ga ketadigan ma'lumotlar
        $payload = [
            "external_id"   => (string) Str::uuid(),
            "amount"        => (int) $data['amount'] * 100,
            "currency_code" => "860",
            "card" => [
                "number" => $card->number,
                "expiry" => $card->expiry,
            ],
            "details" => [
                [
                    "field" => "created_at",
                    "value" => now()->toDateTimeString(),
                ]
            ],
            "payer_data" => [
                "nationality"   => "UZB",
                "first_name"    => $user->first_name,
                "surname"       => $user->last_name,
                "father_name"   => $user->father_name,
                "email"         => $user->email,
                "phone"         => $user->phone ?? null,
            ]
        ];


        // 1. Hamkorbank orqali to'lov yaratish
        $result = HamkorbankService::payCreate($payload);

        // 2. Agar error bo'lsa — error qaytaramiz
        if (isset($result['error'])) {
            return response()->json([
                'status' => 'error',
                'message' => $result['error']['message'] ?? 'Unknown error',
                'code' => $result['error']['code'] ?? null,
            ], 422);
        }

        // 3. Error bo‘lmasa — keyingi bosqich (SMS yoki NONE)
        $method = $result['result']['confirm_method'];

        // 4. SMS bo‘lsa
        if ($method === 'SMS') {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment created successfully, please confirm the payment by SMS code which will be sent to your phone',
                'pay_id' => $result['result']['pay_id'],
                'fee_amount' => $result['result']['fee_amount'],
                'confirmation' => $method,
                'confirmation_info' => 'confirmation method is SMS or NONE',
            ]);
        }

        // 5. NONE bo‘lsa
        if ($method === 'NONE') {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment created successfully, there is no need to confirm the payment',
                'pay_id' => $result['result']['pay_id'],
                'fee_amount' => $result['result']['fee_amount'],
                'confirmation' => $method,
                'confirmation_info' => 'confirmation method is SMS or NONE',
            ]);
        }
    }


    /** ------------------ 🟩 pay.confirm API ------------------ */
    public function confirmPayment(Request $request)
    {
        try {

            $data = $request->validate([
                'pay_id'       => 'required|string',
                'confirm_code' => 'required|string',
            ]);

            $result = HamkorbankService::payConfirm($data);

            if (isset($result['error'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['error']['message'] . ' ' . BankErrorService::getMessage($result['error']['code']),
                    'code' => $result['error']['code'],
                ], 422);
            }


            return response()->json($result, 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() . ' ' . BankErrorService::getMessage($e->getCode()),
                'code'    => $e->getCode(),
            ], 422);
        }
    }



    /** ------------------ 🟧 Resend SMS ------------------ */
    public function resendSms(Request $request)
    {
        try {
            $data = $request->validate([
                'pay_id' => 'required|string'
            ]);

            $result = HamkorbankService::smsResend($data['pay_id']);

            if (isset($result['error'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $result['error']['message'],
                    'code'    => $result['error']['code'],
                ], 422);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'SMS qayta yuborildi',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
            ], 500);
        }
    }


    /** ------------------ 🟦 pay.get API ------------------ */
    public function getPaymentInfo(Request $request)
    {
        try {

            $request->validate([
                'pay_id' => 'required|string'
            ]);

            $result = HamkorbankService::payGet($request->pay_id);

            // Agar error bo‘lsa
            if (isset($result['error'])) {
                return response()->json([
                    'status'  => 'error',
                    'code'    => $result['error']['code'],
                    'message' => $result['error']['message'] . ' ' . BankErrorService::getMessage($result['error']['code']),
                ], 422);
            }

            $state = $result['result']['state'] ?? null;

            $statuses = [
                1 => 'created',
                2 => 'holded',
                3 => 'confirmed',
                4 => 'canceled',
                5 => 'returned'
            ];

            $stateText = $statuses[$state] ?? 'unknown';
            $result['result']['amount'] = number_format($result['result']['amount'] / 100, 2, '.', '');;

            return response()->json([
                'status' => 'success',
                'payment_status' => $stateText,
                'amount' => $result['result']['amount'],
                'data'   => $result['result'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() . ' ' . BankErrorService::getMessage($e->getCode()),
                'code'    => $e->getCode(),
            ], 500);
        }
    }
}
