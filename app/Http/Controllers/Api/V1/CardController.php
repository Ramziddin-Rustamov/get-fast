<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Models\V1\PaymentLog;
use App\Services\V1\HamkorbankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

        DB::beginTransaction();

        $request->validate([
            'number' => 'required|string|min:16|max:19',
            'expiry' => 'required|string|size:4',
            'holder_name' => 'required|string',
            'phone' => 'required',
        ]);


        $last4 = substr($request->number, -4);



        $isExists = Card::where('user_id', auth()->id())
            ->where('phone', $request->phone)
            ->where('expiry', $request->expiry)
            ->where('number', 'LIKE', "%{$last4}")
            ->first();


        if ($isExists) {
            $isExists->delete();
        }

        $messages = [
            'uz' => 'Karta muvaffaqiyatli qo‘shildi. SMS kodi orqali tasdiqlang.',
            'ru' => 'Карта успешно добавлена. Подтвердите с помощью SMS кода.',
            'en' => 'Card added successfully. Verify with SMS code.',
        ];

        $message = $messages[auth()->user()->authLanguage->language] ?? $messages['uz'];


        // $masked = substr($request->number, 0, 6) . '******' . substr($request->number, -4);
        $response = HamkorbankService::addCard($request);

        if (!is_array($response) || !isset($response['result'])) {
            return response()->json([
                'status' => 'error',
                'message' => $response['error']['message'] ?? 'Bank javob bermadi',
                'raw' => $response
            ], 422);
        }

        $maskedHolder = $this->maskHolderName($request->holder_name);

        $card = Card::create([
            'user_id'    => auth()->id(),
            'card_id'    => $response['result']['key'] ?? '1',
            'number'     => $response['result']['number'] ?? '12',
            'expiry'     => $response['result']['expiry'],
            'phone'      => $response['result']['phone'],
            'label'      => $maskedHolder,       // ✅ masklangan holder
            'is_default' => !Card::where('user_id', auth()->id())->exists(),
            'status'     => 'not_verified',
            'meta'       => json_encode($response ?? []),
        ]);





        PaymentLog::create([
            'request' => json_encode($request->all()),
            'user_id' => auth()->id(),
            'response' => json_encode($response),
        ]);

        DB::commit();


        return response()->json([
            'status' => 'success',
            'message' => $message,
            'card' => [
                'id' => $card->id,
                'label' => $card->label, // small typo tuzatildi: 'lable' -> 'label'
                'phone' => $card->phone,
                'key' => $response['result']['key'] ?? null,
            ],
        ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    //DONE ###################### --- DONE -------- #############################
    /** ✅ Kartani verify qilish (SMS kod bilan) */
    public function verifyCard(Request $request)
    {

        try {
            $request->validate([
                'id' => 'required|exists:cards,id',
                'card_key' => 'required',
                'confirm_code' => 'required|string|min:4|max:8',
            ]);

            $card = Card::where('id', $request->id)->first();
            if (!$card) {

                $messages = [
                    'uz' => 'Karta topilmadi',
                    'ru' => 'Карта не найдена',
                    'en' => 'Card not found',
                ];

                $message = $messages[auth()->user()->authLanguage->language] ?? $messages['uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 404);
            }

            $card->status = 'verified';
            $card->save();



            $card = Card::where('id', $request->id)->where('user_id', auth()->id())->first();
            if (!$card) {

                $message = [
                    'uz' => 'Karta topilmadi',
                    'ru' => 'Карта не найдена',
                    'en' => 'Card not found',
                ];
                return response()->json([
                    'status' => 'error',
                    'message' => $message[auth()->user()->authLanguage->language] ?? $message['uz'],
                ], 404);
            }

            $response = HamkorbankService::verifyCard($request);
            // Agar Hamkorbankdan error qaytsa:
            if (isset($response['error'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $response['error']['message'] ?? 'Verification failed',
                ], 400);
            }


            $card->status = 'verified';
            $card->card_id = $response['result']['id'];
            $card->save();

            $messages = [
                'uz' => 'Karta muvaffaqiyatli tasdiqlandi',
                'ru' => 'Карта успешно подтверждена',
                'en' => 'Card verified successfully',
            ];

            $message = $messages[auth()->user()->authLanguage->language] ?? $messages['uz'];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'card' => $response['result'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Boshqa har qanday xatoliklar
            return response()->json([
                'status' => 'error',
                'message' => 'Unexpected error: ' . $e->getMessage(),
            ], 500);
        }
    }


    // DONE ###################### --- DONE -------- #############################
    public function checkCardBalance(Request $request) // return 1 if emaount is exist in this card if not returns 0 
    {
        try {
            $request->validate([
                'id' => 'required|exists:cards,id',
                'card_key' => 'required|string|exists:cards,card_id',
                'amount' => 'required|integer|min:1',
            ]);

            $response = HamkorbankService::checkCardBalance($request);
            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unexpected error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // DONE ###################### --- DONE -------- #############################
    public function myCards()
    {
        $cards = Card::where('user_id', auth()->user()->id)->get();
        if (!$cards) {
            return response()->json([
                'status' => 'error',
                'message' => 'there is no card for this user',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'My cards retrieved successfully',
            'cards' => $cards,
        ]);
    }


    public function maskHolderName(string $name): string
    {
        $parts = explode(' ', $name);

        return collect($parts)->map(function ($part) {
            if (strlen($part) <= 3) {
                return substr($part, 0, 1) . '****';
            }

            return substr($part, 0, 3)
                . str_repeat('*', max(strlen($part) - 6, 2))
                . substr($part, -2);
        })->implode(' ');
    }

    public function deleteCard(Request $request)
    {
        $card = Card::where('id', $request->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$card) {
            return response()->json([
                'status' => 'error',
                'message' => 'Card not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $wasDefault = $card->is_default;

            // Kartani o‘chiramiz
            $card->delete();

            // Agar o‘chirilgan karta default bo‘lsa
            if ($wasDefault) {

                $newDefault = Card::where('user_id', auth()->id())
                    ->first();

                if ($newDefault) {
                    $newDefault->update([
                        'is_default' => true
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Card deleted successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
