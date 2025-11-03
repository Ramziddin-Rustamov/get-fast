<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Models\V1\PaymentLog;
use App\Services\V1\HamkorbankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Svg\Tag\Rect;

class CardController extends Controller
{
    /** ✅ Karta ro‘yxati (foydalanuvchi telefon raqami bilan) */
    //DONE ###################### --- DONE -------- #############################
    public function cardList($phoneNumber)
    {
        return HamkorbankService::cardListForPhoneNumber($phoneNumber);
    }

    /** ✅ Karta qo‘shish */
    //DONE ###################### --- DONE -------- #############################
    public function addCard(Request $request)
    {
        try {
            $request->validate([
                'number' => 'required|string|min:16|max:19|unique:cards,number',
                'expiry' => 'required|string|size:4',
                'holder_name' => 'required|string',
                'phone' => 'required',
            ]);

            $response = HamkorbankService::addCard($request);

            if (!isset($response['result'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $response['error'] ? $response['error']['message'] : 'Bank javob bermadi',
                ], 422);
            }

            DB::beginTransaction();

            // $masked = substr($request->number, 0, 6) . '******' . substr($request->number, -4);

            $card = Card::create([
                'user_id' => auth()->id(),
                'card_id' => $response['result']['key'] ?? '1', // vaqtinchalik key
                'number' => $request->number,
                'expiry' => $request->expiry,
                'phone' => $request->phone,
                'label' => $request->holder_name,
                'is_default' => !Card::where('user_id', auth()->id())->exists(),
                'status' => 'active',
                'meta' => json_encode($response ?? []),
            ]);

            PaymentLog::create([
                'request' => json_encode($request->all()),
                'user_id' => auth()->id(),
                'response' => json_encode($response),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Card added successfully. Verify with SMS if required.',
                'confirm_method' => $response['result']['confirm_method'] ?? 'NONE',
                'confirm_method_info' => 'SMS|NONE|if it is sms then confirm it by verfy method , if NONE then it will not be required',
                'card' => [
                    'id' => $card->id,
                    'lable' => $card->label,
                    'phone' => $card->phone,
                    'key' => $response['result']['key'] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            // // xatolikni logga yozish (debug uchun foydali)
            // Log::error('Card creation failed', [
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /** ✅ Kartani verify qilish (SMS kod bilan) */
    public function verifyCard(Request $request)
    {

        $request->validate([
            'key' => 'required|string',
            'confirm_code' => 'required|string|min:4|max:8',
        ]);
        return HamkorbankService::verifyCard($request);
    }



    public function checkCardBalanceIsAvailable(Request $request)
    {
        $request->validate([
            'card_id' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        return HamkorbankService::checkCardBalanceIsAvailable($request);
    }


    public function checkBalanceByCardId(Request $request)
    {
        return HamkorbankService::checkBalanceByCardId($request);
    }
}
