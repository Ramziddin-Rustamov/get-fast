<?php

namespace App\Http\Controllers\Admin;


use App\Models\User;
use App\Models\V1\Vehicle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use App\Models\V1\Card;
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;
use App\Models\V1\VehicleImages;
use App\Services\V1\HamkorbankService;
use App\Services\V1\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DriverController extends Controller
{

    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }


    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status'); // none, pending, approved, rejected, blocked

        $drivers = User::where('role', 'driver')
            ->with(['balance', 'vehicles', 'driverTrips', 'myVehicle'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                if ($status === 'none') {
                    $query->where('driving_verification_status', 'none');
                } elseif ($status === 'pending') {
                    $query->where('driving_verification_status', 'pending');
                } elseif ($status === 'approved') {
                    $query->where('driving_verification_status', 'approved');
                } elseif ($status === 'rejected') {
                    $query->where('driving_verification_status', 'rejected');
                } elseif ($status === 'blocked') {
                    $query->where('driving_verification_status', 'blocked');
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin-views.drivers.index', compact('drivers', 'status', 'search'));
    }

    /**
     * Bitta driver ma'lumotlarini ko'rsatish
     */
    public function show($driver)
    {

        $driver = User::where('role', 'driver')->with(['balance', 'vehicles', 'driverTrips', 'myVehicle', 'cards'])->find($driver);
        if (!$driver) {
            return redirect()->route('drivers.index')->with('error', 'Haydavchi topilmadi ');
        }

        $vehicles = Vehicle::where('user_id', $driver->id)->get();
        if (empty($vehicles)) {
            return redirect()->view('admin-views.drivers.index')->with('error', 'Moshina topilmadi hozircha !');
        }
        $driverImages = $driver->images; // user_images
        $vehicleImages = VehicleImages::whereIn('vehicle_id', $vehicles->pluck('id'))->get();



        // Oxirgi tranzaksiyalar paginate
        $balanceTransactions = $driver->balanceTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $vehicles = $driver->vehicles()->orderBy('id', 'desc')->paginate(3);
        $trips = $driver->driverTrips()->orderBy('id', 'desc')->paginate(3);
        return view('admin-views.drivers.show', compact(
            'driver',
            'balanceTransactions',
            'vehicles',
            'driverImages',
            'vehicleImages',
            'balanceTransactions'
        ));
    }

    public function destroy(User $driver)
    {
        $driver->delete();
        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully!');
    }



    public function sendSms(Request $request, $driverId)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $message = [
            'uz' => 'Qadam ilovasi adminlaridan xabar: ' . $request->message,
            'ru' => 'Сообщение от администраторов приложения Qadam: ' . $request->message,
            'en' => 'Message from Qadam app administrators: ' . $request->message,
        ];


        $driver = User::where('role', 'driver')->find($driverId);
        $phone = $driver->phone;

        $this->smsService->sendQueued($phone, $message[auth()->user()->authLanguage->language] ?? $message['uz'], 'message-to-driver');


        return redirect()->back()->with('success', 'Xabar muvaffaqiyatli yuborildi ' . $phone . ': ' . $message);
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:none,pending,approved,rejected,blocked',
        ]);

        $driver = User::where('role', 'driver')->where('id', $id)->first();
        $driver->driving_verification_status = $request->status;
        $driver->save();

        $statusTranslations = [
            'none' => [
                'uz' => 'yo‘q',
                'ru' => 'нет',
                'en' => 'none',
            ],
            'pending' => [
                'uz' => 'kutilmoqda',
                'ru' => 'в ожидании',
                'en' => 'pending',
            ],
            'approved' => [
                'uz' => 'tasdiqlandi',
                'ru' => 'одобрено',
                'en' => 'approved',
            ],
            'rejected' => [
                'uz' => 'rad etildi',
                'ru' => 'отклонено',
                'en' => 'rejected',
            ],
            'blocked' => [
                'uz' => 'bloklandi',
                'ru' => 'заблокировано',
                'en' => 'blocked',
            ],
        ];

        $status = $request->status;

        $message = [
            'uz' => 'Sizning haydovchi statusingiz muvaffaqiyatli yangilandi: ' . $statusTranslations[$status]['uz'],
            'ru' => 'Ваш статус водителя успешно обновлён: ' . $statusTranslations[$status]['ru'],
            'en' => 'Your driver status has been successfully updated: ' . $statusTranslations[$status]['en'],
        ];


        // sms logic here
        $this->smsService->sendQueued($driver->phone, $message[$driver->authLanguage->language] ?? $message['uz'], 'message-to-driver-about-driver-status' . $statusTranslations[$status]['uz']);


        return redirect()->back()->with('success', 'Driver status muvaffaqiyatli yangilandi! ' . $statusTranslations[$status]['uz'] . ', va bu haqida foydalanuvchiga xabar yuborildi.');
    }


    public function deleteAllDriverImages($driverId)
    {
        $driver = User::with('images')->findOrFail($driverId);

        foreach ($driver->images as $img) {

            // Faylni storage dan o‘chirish
            $filePath = storage_path('app/public/' . $img->image_path);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // DB dan o‘chiramiz
            $img->delete();
        }

        return back()->with('success', 'Hamma haydovchi rasmlari o‘chirildi');
    }

    public function deleteAllVehicleImages($vehicleId)
    {
        $vehicle = Vehicle::with('images')->findOrFail($vehicleId);

        foreach ($vehicle->images as $img) {

            $filePath = storage_path('app/public/' . $img->image_path);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $img->delete();
        }

        return back()->with('success', 'Barcha moshina rasmlari o‘chirildi');
    }

    public function refund(Request $request, $driverId)
    {
        try {

            DB::beginTransaction();

            $request->validate([
                'card_id' => 'required|exists:cards,id',
                'amount' => 'required|integer',
            ]);

            $driver = User::where('role', 'driver')->find($driverId);
            $driverLanguage = $driver->authLanguage->language ?? 'uz';

            if ($request->amount <= 0) {
                $messages = [
                    'uz' => 'Miqdor 0 dan katta bo\'lishi kerak',
                    'ru' => 'Сумма должна быть больше 0',
                    'en' => 'Amount must be greater than 0',
                ];
                return redirect()->back()->with('error', $messages[$driverLanguage]);
            }

            if ($request->amount > 200000) {
                $messages = [
                    'uz' => 'Miqdor 200000 dan kam bo\'lishi kerak',
                    'ru' => 'Сумма должна быть меньше 200000',
                    'en' => 'Amount must be less than 200000',
                ];
                return redirect()->back()->with('error', $messages[$driverLanguage]);
            }

            if ($request->amount < 1000) {
                $messages = [
                    'uz' => 'Miqdor 1000 dan katta bo\'lishi kerak',
                    'ru' => 'Сумма должна быть больше 1000',
                    'en' => 'Amount must be greater than 1000',
                ];
                return redirect()->back()->with('error', $messages[$driverLanguage]);
            }


            $card = Card::where('id', $request->card_id)
                ->where('user_id', $driver->id)
                ->first();

            if (!$card) {
                $messages = [
                    'uz' => 'Foydalanuvchida karta mavjud emas!',
                    'ru' => 'У пользователя нет карты!',
                    'en' => 'User has no card!'
                ];

                return redirect()->back()->with('error', $messages[$driverLanguage]);
            }

            $amountInKopeyka = $request->amount * 100; // Test summasi

            // Card parametri
            $cardParam = [];
            if (!empty($card->number)) {
                $cardParam['number'] = $card->number;
            } elseif (!empty($card->card_id)) {
                $cardParam['id'] = $card->card_id;
            } else {
                $messages = [
                    'uz' => 'Card raqami yoki ID mavjud emas!',
                    'ru' => 'Номер карты или ID отсутствует!',
                    'en' => 'Card number or ID is missing!',
                ];

                return redirect()->back()->with('error', $messages[$driverLanguage]);
            }

            // Payer_data majburiy, hatto test summasi uchun ham
            $payerData = [
                "surname"     => $driver->last_name ?? 'Test',
                "first_name"  => $driver->first_name ?? 'Test',
                "middle_name" => $driver->father_name ?? 'Test',
            ];

            $data = [
                "ext_id"     => (string) Str::uuid(),
                "amount"     => $amountInKopeyka,
                "card"       => $cardParam,
                "payer_data" => $payerData,
            ];



            $formattedAmount = number_format($amountInKopeyka / 100, 0, '.', ''); // 10000 ko‘pdan 100 ga bo‘linadi

            $refundMessage = [
                'uz' => "Pul muvaffaqiyatli qaytarildi. Karta: {$card->number}, summa: {$formattedAmount} so'm",
                'ru' => "Средства успешно возвращены. Карта: {$card->number}, сумма: {$formattedAmount} сум",
                'en' => "Refund successful. Card: {$card->number}, amount: {$formattedAmount} UZS",
            ];


            $userBalanceBefore = $driver->balance->balance;
            $driver->balance->update([
                'balance' => $driver->balance->balance - ($amountInKopeyka / 100),
            ]);

            // DB-ga yozish uchun misol
            BalanceTransaction::create([
                'user_id'    => $driver->id,
                'type'    => 'debit',
                'amount'     => $amountInKopeyka / 100, // summani so‘mga o‘tkazish
                'balance_before' => $userBalanceBefore,
                'balance_after'  => $userBalanceBefore - $amountInKopeyka / 100,
                'status'     => 'success',
                'reason' => $refundMessage[$driverLanguage ?? 'uz'],
            ]);
            $compBalance = CompanyBalance::lockForUpdate()->firstOrCreate();
            $compBalanceBefore = $compBalance->balance;
            $compBalance->decrement('balance', $amountInKopeyka / 100);

            $refundReasonForCompany = [
                'uz' => "Pul muvaffaqiyatli qaytarildi. Karta: {$card->number}, summa: {$formattedAmount} so'm" . $driver->first_name . "va" . "telefon raqami" . " " . $driver->phone,
                'ru' => "Средства успешно возвращены. Карта: {$card->number}, сумма: {$formattedAmount} сум" . $driver->first_name . "va" . "telefon raqami" . " " . $driver->phone,
                'en' => "Refund successful. Card: {$card->number}, amount: {$formattedAmount} UZS" . $driver->first_name . "va" . "telefon raqami" . " " . $driver->phone,
            ];

            $companyBalanceTraction = CompanyBalanceTransaction::create([
                'company_balance_id' => $compBalance->id,
                'amount' => $amountInKopeyka / 100,
                'balance_before' => $compBalanceBefore ?? 0,
                'balance_after' => $compBalanceBefore - $amountInKopeyka / 100,
                'trip_id' => null,
                'booking_id' => null,
                'type' => 'outgoing',
                'reason' => $refundReasonForCompany['uz'],
                'currency' => 'UZS',
            ]);


            // smsni navbatga yuborish
            $this->smsService->sendQueued($driver->phone, $refundMessage[$driverLanguage ?? 'uz'], 'refund-driver-by-admins');

            $messages = [
                'uz' => 'Pul muvaffaqiyatli qaytarildi',
                'ru' => 'Средства успешно возвращены',
                'en' => 'Refund successful',
            ];


            $token = HamkorbankService::getToken();
            if (!$token) {
                $messages = [
                    'uz' => 'Token olinmadi',
                    'ru' => 'Токен не получен',
                    'en' => 'Token not found',
                ];

                return redirect()->back()->with('error', $messages[$driverLanguage ?? 'uz']);
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
                return redirect()->back()->with('error', 'pulni qaytarishda Hmakor bank bilan xatolik yuz berdi');
            }

            DB::commit();

            return redirect()->back()->with('success', $messages[$driverLanguage]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function withdrawFromUser(Request $request, $userID)
    {


        try {

            $request->validate([
                'amount' => 'required|numeric|min:1',
                'note'   => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();
            $user = User::findOrFail($userID);

            $userBalance = UserBalance::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$userBalance) {
                throw new \Exception('User balance not found');
            }

            if ($userBalance->balance < $request->amount) {
                throw new \Exception('Insufficient balance');
            }

            $beforeUserBalance = $userBalance->balance;
            $afterUserBalance  = $beforeUserBalance - $request->amount;

            $userBalance->update([
                'balance' => $afterUserBalance
            ]);

            $message = [
                'uz' => 'Pul hisobingizdan yechildi. Izoh: ' . $request->note,
                'ru' => 'Средства списаны с вашего счета. Примечание: ' . $request->note,
                'en' => 'Funds have been withdrawn from your account. Note: ' . $request->note,
            ];

            $lang = $user->authLanguage->language ?? 'uz';

            BalanceTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'debit',
                'amount'         => $request->amount,
                'balance_before' => $beforeUserBalance,
                'balance_after'  => $afterUserBalance,
                'status'         => 'success',
                'reason'         => $message[$lang],
            ]);

            $companyBalance = CompanyBalance::lockForUpdate()
                ->firstOrCreate(
                    [], // search criteria, agar faqat bitta row bo‘lsa bo‘sh array yetarli
                    ['balance' => 0, 'total_income' => 0]
                );
            $beforeCompanyBalance = $companyBalance->balance;
            $afterCompanyBalance  = $beforeCompanyBalance + $request->amount;


            $companyBalance->increment('balance', $request->amount);
            $companyBalance->increment('total_income', $request->amount);


            CompanyBalanceTransaction::create([
                'company_balance_id' => $companyBalance->id,
                'amount'             => $request->amount,
                'balance_before'     => $beforeCompanyBalance,
                'balance_after'      => $afterCompanyBalance,
                'type'               => 'incoming',
                'reason'             => 'Withdraw from user: ' . $user->id . ' | Note: ' . $request->note,
                'currency'           => 'UZS',
            ]);

            DB::commit();

            return back()->with('success', $message[$lang]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }


    //   for clients and drivers 
    public function payToUserToBalance(Request $request, $userID)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($userID);

            $userBalance = UserBalance::lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0]
                );


            // 🔒 Lock company balance
            $companyBalance = CompanyBalance::lockForUpdate()->first();

            if (!$companyBalance) {
                throw new \Exception('Company balance not found');
            }


            // Save BEFORE values
            $beforeUserBalance    = $userBalance->balance;
            $afterUserBalance     = $beforeUserBalance + $request->amount;

            $beforeCompanyBalance = $companyBalance->balance;
            $afterCompanyBalance  = $beforeCompanyBalance - $request->amount;

            // Update balances
            $userBalance->update(['balance' => $afterUserBalance]);
            $companyBalance->update(['balance' => $afterCompanyBalance]);

            $message = [
                'uz' => 'Admin tomonidan hisobingizga ' . $request->amount . ' so‘m qo‘shildi. Izoh: ' . $request->note,
                'ru' => 'Администратор пополнил ваш баланс на ' . $request->amount . '. Примечание: ' . $request->note,
                'en' => 'Admin credited your balance with ' . $request->amount . '. Note: ' . $request->note,
            ];

            $lang = $user->authLanguage->language ?? 'uz';

            // User transaction
            BalanceTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'credit',
                'amount'         => $request->amount,
                'balance_before' => $beforeUserBalance,
                'balance_after'  => $afterUserBalance,
                'status'         => 'success',
                'reason'         => $message[$lang],
            ]);

            // Company transaction
            CompanyBalanceTransaction::create([
                'company_balance_id' => $companyBalance->id,
                'amount'             => $request->amount,
                'balance_before'     => $beforeCompanyBalance,
                'balance_after'      => $afterCompanyBalance,
                'type'               => 'outgoing',
                'reason'             => 'Admin payment to user ID ' . $user->id . '. Note: ' . $request->note,
                'currency'           => 'UZS',
            ]);

            DB::commit();

            return back()->with('success', $message[$lang]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
