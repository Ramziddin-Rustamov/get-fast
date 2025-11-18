<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Services\V1\HamkorbankService;
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
            "amount"        => (int) $data['amount'],
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
                "middle_name"   => "",
                "person_code"   => "",
                "birth_date"    => "",
                "phone"         => $user->phone ?? null,
            ]
        ];


        // 5. Hamkorbank orqali to'lov yaratish
        $result = HamkorbankService::payCreate($payload);




        return response()->json($result);
    }


    /** ------------------ 🟩 pay.confirm API ------------------ */
    public function confirmPayment(Request $request)
    {
        $data = $request->validate([
            'pay_id'       => 'required|string',
            'confirm_code' => 'nullable|string',
            'hold'         => 'nullable|boolean',
        ]);

        $result = HamkorbankService::payConfirm($data);

        return response()->json($result);
    }
}
