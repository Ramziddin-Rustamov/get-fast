<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentHistoryRepository;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use App\Models\V1\Card;
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;
use App\Models\V1\Payment;
use App\Services\V1\BankErrorService;
use App\Services\V1\HamkorbankService;
use App\Services\V1\SmsService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{

    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }



    public function createPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user(); // kim to'layotganini olish

            // 1. Faqat amount validatsiya
            $data = $request->validate([
                'amount' => 'required|numeric|min:1000',
                'card_id' => 'nullable|exists:cards,id',
            ]);


            if ($data['card_id']) {
                $card = Card::where('id', $data['card_id'])->where('user_id', $user->id)->where('status', 'verified')->first();
            } else {
                // 2. Userning default kartasini olish
                $card = Card::where('user_id', $user->id)
                    ->where('is_default', 1)->where('status', 'verified')
                    ->first();
            }

            if (!$card) {
                $messages = [
                    'uz' => 'Foydalanuvchida karta mavjud emas!',
                    'ru' => 'У пользователя нет карты!',
                    'en' => 'User has no card!'
                ];

                $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

                return response()->json([
                    'status' => 'error',
                    'message' => $message
                ], 400);
            }


            // 3. Check balance: karta parametrlari + amount retur 0 or 1 
            $check = HamkorbankService::checkCardBalance([
                'card_key'    => $card->card_id,
                'amount'      => $data['amount'],
            ]);


            if ($check == 0) { // 0 - balans yetarli emas
                $messages = [
                    'uz' => "Karta balansida mablag‘ yetarli emas",
                    'ru' => "На балансе карты недостаточно средств",
                    'en' => "Insufficient funds on card balance"
                ];

                $message = $messages[auth()->user()->authLanguage->language] ?? $messages['uz'];

                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }

            // 4. Payment yaratish uchun API-ga ketadigan ma'lumotlar
            $payload = [
                "external_id"   => (string) Str::uuid(),
                "amount"        => (int) $data['amount'] * 100,
                "currency_code" => "860",
                "card" => [
                    'id' => $card->card_id,
                ],
                "details" => [
                    [
                        "field" => "created_at",
                        "value" => now()->toDateTimeString(),
                    ]
                ],
                "payer_data" => [
                    "nationality"   => "UZB",
                    "first_name"    => $user->first_name ?? null,
                    "surname"       => $user->last_name ?? null,
                    "middle_name"   => $user->father_name ?? null
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



                $payment = new Payment();
                $payment->user_id = $user->id;
                $payment->card_id = $user->isDefaultCard->id;
                $payment->pay_id = $result['result']['pay_id'];
                $payment->status = $method === 'SMS' ? 'created' : 'confirmed';
                $payment->amount = $data['amount'];
                $payment->payment_method = 'Hamkorbank by card';
                $payment->save();
                DB::commit();

                $messages = [
                    'uz' => 'To‘lov muvaffaqiyatli yaratildi, iltimos, telefoningizga yuboriladigan SMS kodi orqali tasdiqlang',
                    'ru' => 'Платёж успешно создан, пожалуйста, подтвердите платёж с помощью SMS кода, который будет отправлен на ваш телефон',
                    'en' => 'Payment created successfully, please confirm the payment by SMS code which will be sent to your phone',
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => $messages[auth()->user()->authLanguage->language ?? 'uz'],
                    'pay_id' => $result['result']['pay_id'],
                    'fee_amount' => $result['result']['fee_amount'],
                    'confirmation' => $method,
                    'confirmation_info' => 'confirmation method is SMS or NONE',
                ]);
            }

            // 5. NONE bo‘lsa
            if ($method === 'NONE') {


                $payment = new Payment();
                $payment->user_id = $user->id;
                $payment->card_id = $user->isDefaultCard->id;
                $payment->pay_id = $result['result']['pay_id'];
                $payment->status = $method === 'SMS' ? 'created' : 'confirmed';
                $payment->amount = $data['amount'];
                $payment->payment_method = 'by card';
                $payment->save();
                DB::commit();


                // it will be confirmed automatically then we don't need to confirm to make payment
                $payment = Payment::where('pay_id', $result['result']['pay_id'])->where('status', 'confirmed')->where('user_id', $user->id)->first();
                if ($payment) {
                    $trx = new BalanceTransaction();
                    $trx->user_id = $user->id;
                    $trx->type = 'credit';
                    $trx->amount =  $user->balance->balance + $payment->amount;
                    $trx->balance_before = $user->balance->balance;
                    $trx->balance_after = $user->balance->balance + $payment->amount;
                    $trx->trip_id = null;
                    $trx->status = 'success';
                    $reasons = [
                        'uz' => 'Balans foydalanuvchi tomonidan qo‘lda to‘ldirildi va SMS tasdiqlashsiz tasdiqlandi',
                        'ru' => 'Баланс пополнен вручную пользователем и подтвержден без SMS',
                        'en' => 'Balance filled manually by user and confirmed without SMS'
                    ];

                    $trx->reason = $reasons[auth()->user()->authLanguage->language] ?? $messages['uz'];
                    $trx->reference_id = null;
                    $trx->save();


                    $userBalance = UserBalance::where('user_id', $user->id)
                        ->lockForUpdate()
                        ->firstOrCreate(
                            ['user_id' => $user->id],
                            ['balance' => 0] // default
                        );

                    $userBalance->user_id = $user->id;
                    $userBalance->balance = $user->balance->balance + $payment->amount;
                    $userBalance->save();
                    DB::commit();

                    $messages = [
                        'uz' => 'To‘lov muvaffaqiyatli yaratildi, to‘lovni tasdiqlash shart emas',
                        'ru' => 'Платёж успешно создан, подтверждение платежа не требуется',
                        'en' => 'Payment created successfully, there is no need to confirm the payment',
                    ];

                    $messagereturn = $messages[auth()->user()->authLanguage->language] ?? $messages['uz'];

                    return response()->json([
                        'status' => 'success',
                        'message' => $messagereturn,
                        'pay_id' => $result['result']['pay_id'],
                        'fee_amount' => $result['result']['fee_amount'],
                        'confirmation' => $method,
                        'confirmation_info' => 'confirmation method is SMS or NONE',
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'payment status now' . ' ' . $payment->status
                ]);
            }
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /** ------------------ 🟩 pay.confirm API ------------------ */
    public function confirmPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validate([
                'pay_id'       => 'required|string',
                'confirm_code' => 'required|string',
            ]);

            // Hamkorbank pay get
            $resultOfConfirm = HamkorbankService::payGet($data['pay_id']);

            if (isset($resultOfConfirm['result']) && $resultOfConfirm['result']['state'] != 1) {

                $resultData = $resultOfConfirm['result'];

                $amount = number_format($resultData['amount'] / 100, 2, '.', '');

                $stateText = match ($resultData['state']) {
                    1 => 'Created',
                    2 => 'Pending',
                    3 => 'Confirmed',
                    4 => 'Failed',
                    default => 'Unknown',
                };

                $payment = Payment::firstWhere([
                    'pay_id'  => $data['pay_id'],
                    'user_id' => auth()->id(),
                ]);

                $payment?->update(['status' => $stateText]);

                DB::commit();

                $messages = [
                    'uz' => 'To‘lov holati allaqachon o‘zgartirilgan',
                    'ru' => 'Статус платежа уже изменён',
                    'en' => 'Payment status already changed',
                ];

                $message = $messages[auth()->user()->authLanguage->language] ?? $messages['uz'];

                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'payment_status' => $stateText,
                    'amount' => $amount,
                    'created_at' => $resultData['created_at'],
                    'card' => $resultData['card']['number'],
                ]);
            }

            // PAY CONFIRM
            $result = HamkorbankService::payConfirm($data);

            if (isset($result['error'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['error']['message'] . ' ' . BankErrorService::getMessage($result['error']['code']),
                    'code' => $result['error']['code'],
                ], 422);
            }

            // STATE MAP
            $state = $result['result']['state'] ?? null;

            $statuses = [
                1 => 'created',
                2 => 'holded',
                3 => 'confirmed',
                4 => 'canceled',
                5 => 'returned'
            ];

            $stateText = $statuses[$state] ?? 'unknown';

            // PAYMENT UPDATE
            $payment = Payment::firstWhere([
                'pay_id'  => $data['pay_id'],
                'user_id' => auth()->id(),
            ]);

            $payment?->update(['status' => $stateText]);

            // BALANCE TRANSACTION
            $user = auth()->user();

            $trx = new BalanceTransaction();
            $trx->user_id = $user->id;
            $trx->type = 'credit';
            $trx->amount =  $user->balance->balance + $payment->amount;
            $trx->balance_before = $user->balance->balance;
            $trx->balance_after = $user->balance->balance + $payment->amount;
            $trx->trip_id = null;
            $trx->status = 'success';
            $reasons = [
                'uz' => 'Balans foydalanuvchi tomonidan qo‘lda to‘ldirildi va SMS orqali tasdiqlandi',
                'ru' => 'Баланс пополнен вручную пользователем и подтверждён через SMS',
                'en' => 'Balance filled manually by user and confirmed by SMS'
            ];

            $trx->reason = $reasons[auth()->user()->authLanguage->language] ?? $reasons['uz'];

            $trx->reference_id = null;
            $trx->save();


            $userBalance = UserBalance::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0] // default
                );


            $userBalance->user_id = $user->id;
            $userBalance->balance = $user->balance->balance + $payment->amount;
            $userBalance->save();

            DB::commit();

            $messages = [
                'uz' => 'To‘lov muvaffaqiyatli tasdiqlandi',
                'ru' => 'Платёж успешно подтверждён',
                'en' => 'Payment confirmed successfully',
            ];

            $message = $messages[auth()->user()->authLanguage->language] ?? $messages['uz'];

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'payment_status' => $stateText,
                'card' => $result['result']['card']['number'],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

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

            $messages = [
                'uz' => 'SMS qayta yuborildi',
                'ru' => 'SMS отправлено повторно',
                'en' => 'SMS resent successfully',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'success',
                'message' => $message,
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


    public function getPaymentHistory()
    {
        $payment = Payment::where('user_id', auth()->user()->id)->get();
        if (!$payment) {
            $messages = [
                'uz' => 'To‘lovlar tarixi topilmadi',
                'ru' => 'История платежей не найдена',
                'en' => 'Payment history not found',
            ];

            $message = $messages[auth()->user()->authLanguage->language ?? 'uz'];

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 404);
        }
        return  PaymentHistoryRepository::collection($payment);
    }



    public function refund(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'card_id' => 'required|exists:cards,id',
                'amount' => 'required|integer',
            ]);

            $user = auth()->user();
            $userLanguage = auth()->user()->authLanguage->language ?? 'uz';

            if ($user->balance->balance < $request->amount) {
                $message = [
                    'uz' => 'Siz hisobingizdagi mablag‘dan ko‘proq pul yecha olmaysiz!',
                    'ru' => 'Вы не можете вывести сумму, превышающую баланс на вашем счёте!',
                    'en' => 'You cannot withdraw an amount greater than your account balance!',
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $message[$userLanguage]
                ]);
            }



            if ($request->amount <= 0) {
                $messages = [
                    'uz' => 'Miqdor 0 dan katta bo\'lishi kerak',
                    'ru' => 'Сумма должна быть больше 0',
                    'en' => 'Amount must be greater than 0',
                ];
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$userLanguage],
                ]);
            }

            if ($request->amount > 200000) {
                $messages = [
                    'uz' => 'Miqdor 200000 dan kam bo\'lishi kerak',
                    'ru' => 'Сумма должна быть меньше 200000',
                    'en' => 'Amount must be less than 200000',
                ];
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$userLanguage],
                ]);
            }

            if ($request->amount < 1000) {
                $messages = [
                    'uz' => 'Miqdor 1000 dan katta bo\'lishi kerak',
                    'ru' => 'Сумма должна быть больше 1000',
                    'en' => 'Amount must be greater than 1000',
                ];
                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$userLanguage],
                ]);
            }



            $card = Card::where('id', $request->card_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$card) {
                $messages = [
                    'uz' => 'Foydalanuvchida karta mavjud emas!',
                    'ru' => 'У пользователя нет карты!',
                    'en' => 'User has no card!'
                ];

                return response()->json([
                    'status' => 'error',
                    'message' => $messages[$userLanguage ?? 'uz'],
                ], 404);
            }

            $amountInKopeyka = $request->amount * 100; // Test summasi

            // Card parametri
            $cardParam = [];
            if (!empty($card->card_id)) {
                $cardParam['id'] = $card->card_id;
            } else {
                $messages = [
                    'uz' => 'Card raqami yoki ID mavjud emas!',
                    'ru' => 'Номер карты или ID отсутствует!',
                    'en' => 'Card number or ID is missing!',
                ];

                return response()->json([
                    'status'  => 'error',
                    'message' => $messages[$userLanguage],
                ], 400);
            }


            $formattedAmount = number_format($amountInKopeyka / 100, 0, '.', ''); // 10000 ko‘pdan 100 ga bo‘linadi

            $refundMessage = [
                'uz' => "Pul muvaffaqiyatli qaytarildi. Karta: {$card->number}, summa: {$formattedAmount} so'm",
                'ru' => "Средства успешно возвращены. Карта: {$card->number}, сумма: {$formattedAmount} сум",
                'en' => "Refund successful. Card: {$card->number}, amount: {$formattedAmount} UZS",
            ];


            $userBalanceBefore = $user->balance->balance;
            $user->balance->update([
                'balance' => $user->balance->balance - ($amountInKopeyka / 100),
            ]);

            // DB-ga yozish uchun misol
            BalanceTransaction::create([
                'user_id'    => $user->id,
                'type'    => 'debit',
                'amount'     => $amountInKopeyka / 100, // summani so‘mga o‘tkazish
                'balance_before' => $userBalanceBefore,
                'balance_after'  => $userBalanceBefore - $amountInKopeyka / 100,
                'status'     => 'success',
                'reason' => $refundMessage[$userLanguage ?? 'uz'],
            ]);

            $compBalance = CompanyBalance::lockForUpdate()->firstOrCreate([], ['balance' => 0, 'total_income' => 0]);

            $compBalanceBefore = $compBalance->balance;
            $compBalance->decrement('balance', $amountInKopeyka / 100);

            $refundReasonForCompany = [
                'uz' => "Pul muvaffaqiyatli qaytarildi. Karta: {$card->number}, summa: {$formattedAmount} so'm" . $user->first_name . "va" . "telefon raqami" . " " . $user->phone,
                'ru' => "Средства успешно возвращены. Карта: {$card->number}, сумма: {$formattedAmount} сум" . $user->first_name . "va" . "telefon raqami" . " " . $user->phone,
                'en' => "Refund successful. Card: {$card->number}, amount: {$formattedAmount} UZS" . $user->first_name . "va" . "telefon raqami" . " " . $user->phone,
            ];

            $companyBalanceTraction = CompanyBalanceTransaction::create([
                'company_balance_id' => $compBalance->id,
                'amount' => $amountInKopeyka / 100,
                'balance_before' => $compBalanceBefore,
                'balance_after' => $compBalanceBefore - $amountInKopeyka / 100,
                'trip_id' => null,
                'booking_id' => null,
                'type' => 'outgoing',
                'reason' => $refundReasonForCompany['uz'],
                'currency' => 'UZS',
            ]);

            $messages = [
                'uz' => 'Pul muvaffaqiyatli qaytarildi',
                'ru' => 'Средства успешно возвращены',
                'en' => 'Refund successful',
            ];


            // Payer_data majburiy, hatto test summasi uchun ham
            $payerData = [
                "surname"     => $user->last_name ?? 'Test',
                "first_name"  => $user->first_name ?? 'Test',
                "middle_name" => $user->father_name ?? 'Test',
            ];

            $data = [
                "ext_id"     => (string) Str::uuid(),
                "amount"     => $amountInKopeyka,
                "card"       => $cardParam,
                "payer_data" => $payerData,
            ];

            $token = HamkorbankService::getToken();
            if (!$token) {
                $messages = [
                    'uz' => 'Token olinmadi',
                    'ru' => 'Токен не получен',
                    'en' => 'Token not found',
                ];

                return response()->json([
                    'status'  => 'error',
                    'message' => $messages[$userLanguage],
                ], 500);
            }

            $payload = [
                "jsonrpc" => "2.0",
                "method"  => "pay.a2c",
                "params"  => [$data],
                "id"      => (string) Str::uuid(),
            ];

            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(HamkorbankService::baseUrl(), $payload);

            $result = $response->json();

            if (isset($result['error'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $result['error']['message'] ?? 'Unknown error',
                    'code'    => $result['error']['code'] ?? null
                ], 400);
            }

            $state = $result['result']['state'] ?? null;

            if ($state != 5) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pul qaytarishda xatolik yuz berdi',
                    'code'    => $result['error']['code'] ?? null
                ], 400);
            }

            $this->smsService->sendQueued($user->phone, $refundMessage[$userLanguage ?? 'uz'], 'refund-message-to-user');


            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => $messages[$userLanguage ?? 'uz'],
                'data'    => $result
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() . ' ' . BankErrorService::getMessage($e->getCode()),
                'code'    => $e->getCode(),
            ], 500);
        }
    }



    public function getBalance()
    {
        $user = auth()->user();

        if ($user->balance->balance) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'balance' => $user->balance->balance,
                    'currency' => 'UZS'
                ],
            ]);
        }

        return UserBalance::firstOrCreate([
            'user_id' => $user->id,
            'balance' => 0
        ]);
    }
}
