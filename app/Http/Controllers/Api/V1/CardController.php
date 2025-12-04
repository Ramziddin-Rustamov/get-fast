<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Models\V1\PaymentLog;
use App\Services\V1\HamkorbankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Svg\Tag\Rect;

class CardController extends Controller
{
    private $language;
    public function __construct()
    {
        $this->language = auth()->user()->authLanguage->language ?? 'uz';
    }
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
                'status' => 'not_verified',
                'meta' => json_encode($response ?? []),
            ]);

            PaymentLog::create([
                'request' => json_encode($request->all()),
                'user_id' => auth()->id(),
                'response' => json_encode($response),
            ]);

            DB::commit();


            $messages = [
                'uz' => 'Karta muvaffaqiyatli qo‘shildi. SMS kodi orqali tasdiqlang.',
                'ru' => 'Карта успешно добавлена. Подтвердите с помощью SMS кода.',
                'en' => 'Card added successfully. Verify with SMS code.',
            ];

            $message = $messages[$this->language];

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


            $response = HamkorbankService::verifyCard($request);

            $card = Card::where('id', $request->id)->first();
            if (!$card) {

                $messages = [
                    'uz' => 'Karta topilmadi',
                    'ru' => 'Карта не найдена',
                    'en' => 'Card not found',
                ];
                
                $message = $messages[$this->language];
                
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 404);
                
            }

            $card->status = 'verified';
            $card->save();

            // Agar Hamkorbankdan error qaytsa:
            if (isset($response['error'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $response['error']['message'] ?? 'Verification failed',
                ], 400);
            }

            $card = Card::where('id', $request->id)->first();
            $card->status = 'verified';
            $card->card_id = $response['result']['id'];
            $card->save();

            $messages = [
                'uz' => 'Karta muvaffaqiyatli tasdiqlandi',
                'ru' => 'Карта успешно подтверждена',
                'en' => 'Card verified successfully',
            ];
            
            $message = $messages[$this->language];
            
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'card' => $response['result'] ?? null,
            ]);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // HTTP so‘rov bilan bog‘liq xatoliklar
            return response()->json([
                'status' => 'error',
                'message' => 'Request error: ' . $e->getMessage(),
            ], 500);
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


    public function myCards()
    {
        $cards = Card::where('user_id', auth()->user()->id)->get();
        if(!$cards){
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
}
