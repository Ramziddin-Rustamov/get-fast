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
use App\Models\V1\Booking;
use App\Models\V1\Trip;
use App\Models\V1\BookingPassengers;
use App\Services\V1\HamkorbankService;
use App\Services\V1\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

 



    public function sendSms(Request $request, $driverId)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $message = [
            'uz' => 'ketamiz.com ilovasi adminlaridan xabar: ' . $request->message,
            'ru' => 'Сообщение от администраторов приложения ketamiz.com: ' . $request->message,
            'en' => 'Message from ketamiz.com app administrators: ' . $request->message,
        ];


        $driver = User::where('role', 'driver')->find($driverId);
        $phone = $driver->phone;

        $this->smsService->sendQueued($phone, $message[$driver->authLanguage->language] ?? $message['uz'], 'message-to-driver');


        return redirect()->back()->with('success',
        'Xabar muvaffaqiyatli yuborildi ' . ($phone ?? '') . ': ' . $request->message
    );
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:none,pending,approved,rejected,blocked',
        ]);

        $driver = User::where('role', 'driver')->where('id', $id)->first();

        if ($request->status == 'none' || $request->status == 'rejected' || $request->status == 'blocked') {
            $driver->role = 'client';
            $driver->driving_verification_status = $request->status;
        }

        if ($request->status == 'approved') {
            $driver->role = 'driver';
            $driver->driving_verification_status = $request->status;
        }

        if ($request->status == 'pending') {
            $driver->role = 'driver';
            $driver->driving_verification_status = $request->status;
        }

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
        // $this->smsService->sendQueued($driver->phone, $message[$driver->authLanguage->language] ?? $message['uz'], 'message-to-driver-about-driver-status' . $statusTranslations[$status]['uz']);


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
                'id' => 'required|exists:cards,id',
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


            $card = Card::where('id', $request->id)
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
            if (!empty($card->card_id)) {
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

            // Agar mavjud bo'lmasa, yaratish
            if (!$companyBalance) {
                $companyBalance = CompanyBalance::create([
                    'balance' => 0,        // boshlang'ich balans
                    'total_income' => 0,   // boshlang'ich total income
                ]);
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

    public function trips($driverId)
    {
        $driver = User::with([
            'driverTrips.startQuarter.district',
            'driverTrips.endQuarter.district',
            'driverTrips.bookings.user',
            'driverTrips.bookings.passengers',
        ])->findOrFail($driverId);

        return view('admin-views.drivers.trips', compact('driver'));
    }

    public function documents($driverId)
    {
        $driver = User::with('images')->findOrFail($driverId);
        $driverImages = $driver->images;

        return view('admin-views.drivers.documents', compact('driver', 'driverImages'));
    }

    /**
     * Haydovchi hujjatlarini ZIP qilib yuklab olish.
     * ZIP ichida "Ism Familiya Telefon" nomli papka, unda tegishli rasmlar.
     */
    public function downloadDocuments($driverId)
    {
        $driver = User::with('images')->findOrFail($driverId);
        $images = $driver->images;

        if ($images->isEmpty()) {
            return back()->with('error', 'Yuklab olish uchun hujjatlar mavjud emas.');
        }

        // Papka nomi: Ism Familiya Telefon (xavfsiz belgilar)
        $folderName = trim("{$driver->first_name} {$driver->last_name} {$driver->phone}");
        $folderName = preg_replace('/[^\p{L}\p{N}_\-\+ ]/u', '', $folderName);
        $folderName = trim($folderName) ?: ('driver_' . $driver->id);

        $tmpPath = storage_path('app/' . $folderName . '_' . time() . '.zip');

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'ZIP fayl yaratib bo‘lmadi.');
        }

        $counts = [];
        foreach ($images as $img) {
            $absolute = Storage::disk('public')->path($img->image_path);
            if (!is_file($absolute)) {
                continue;
            }

            $ext = pathinfo($absolute, PATHINFO_EXTENSION) ?: 'jpg';
            $label = str_replace('_', '-', $img->type) . ($img->side ? '_' . $img->side : '');

            // Bir xil nomdagi fayllar uchun raqam qo'shish
            $counts[$label] = ($counts[$label] ?? 0) + 1;
            $suffix = $counts[$label] > 1 ? '_' . $counts[$label] : '';

            $zip->addFile($absolute, $folderName . '/' . $label . $suffix . '.' . $ext);
        }
        $zip->close();

        if (!is_file($tmpPath) || filesize($tmpPath) === 0) {
            @unlink($tmpPath);
            return back()->with('error', 'Hujjat fayllari topilmadi.');
        }

        return response()->download($tmpPath, $folderName . '.zip')->deleteFileAfterSend(true);
    }

    /**
     * Moshina rasmlarini ZIP qilib yuklab olish.
     * ZIP ichida "Ism Familiya Telefon - Model Raqami" nomli papka, unda rasmlar.
     */
    public function downloadVehicleImages($vehicleId)
    {
        $vehicle = Vehicle::with(['images', 'user'])->findOrFail($vehicleId);
        $images = $vehicle->images;

        if ($images->isEmpty()) {
            return back()->with('error', 'Yuklab olish uchun moshina rasmlari mavjud emas.');
        }

        $driver = $vehicle->user;
        $folderName = trim("{$driver?->first_name} {$driver?->last_name} {$driver?->phone} - {$vehicle->model} {$vehicle->car_number}");
        $folderName = preg_replace('/[^\p{L}\p{N}_\-\+ ]/u', '', $folderName);
        $folderName = trim($folderName) ?: ('vehicle_' . $vehicle->id);

        $tmpPath = storage_path('app/' . $folderName . '_' . time() . '.zip');

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'ZIP fayl yaratib bo‘lmadi.');
        }

        $counts = [];
        foreach ($images as $img) {
            $absolute = Storage::disk('public')->path($img->image_path);
            if (!is_file($absolute)) {
                continue;
            }

            $ext = pathinfo($absolute, PATHINFO_EXTENSION) ?: 'jpg';
            $label = str_replace('_', '-', $img->type) . ($img->side ? '_' . $img->side : '');

            $counts[$label] = ($counts[$label] ?? 0) + 1;
            $suffix = $counts[$label] > 1 ? '_' . $counts[$label] : '';

            $zip->addFile($absolute, $folderName . '/' . $label . $suffix . '.' . $ext);
        }
        $zip->close();

        if (!is_file($tmpPath) || filesize($tmpPath) === 0) {
            @unlink($tmpPath);
            return back()->with('error', 'Moshina rasm fayllari topilmadi.');
        }

        return response()->download($tmpPath, $folderName . '.zip')->deleteFileAfterSend(true);
    }

    public function vehiclesPage($driverId)
    {
        $driver = User::findOrFail($driverId);

        $vehicles = $driver->vehicles()->with('color')->orderBy('id', 'desc')->paginate(6);
        $vehicleImages = VehicleImages::whereIn('vehicle_id', $vehicles->pluck('id'))->get();

        return view('admin-views.drivers.vehicles', compact('driver', 'vehicles', 'vehicleImages'));
    }

    public function transactions($driverId)
    {
        $driver = User::findOrFail($driverId);

        $balanceTransactions = $driver->balanceTransactions()
            ->with([
                'trip.startQuarter', 'trip.endQuarter',
                'trip.startDistrict', 'trip.endDistrict',
                'trip.startRegion', 'trip.endRegion',
                'trip.startPoint', 'trip.endPoint',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin-views.drivers.transactions', compact('driver', 'balanceTransactions'));
    }

    /**
     * Admin tomonidan bitta yo'lovchini (passenger) bekor qilish.
     * Pul clientga qaytariladi (xizmat haqqi ushlab qolinadi),
     * haydovchidan yechib olinadi, hammasi balance_transactions ga yoziladi.
     * Reason har bir foydalanuvchining tilida yoziladi.
     */
    public function cancelPassenger($bookingId, $passengerId)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::where('id', $bookingId)->lockForUpdate()->first();
            if (!$booking) {
                DB::rollBack();
                return back()->with('error', 'Buyurtma topilmadi');
            }

            $trip = Trip::where('id', $booking->trip_id)->lockForUpdate()->first();
            if (!$trip) {
                DB::rollBack();
                return back()->with('error', 'Trip topilmadi');
            }

            $passenger = BookingPassengers::where('id', $passengerId)
                ->where('booking_id', $booking->id)
                ->lockForUpdate()
                ->first();
            if (!$passenger) {
                DB::rollBack();
                return back()->with('error', 'Yo‘lovchi topilmadi');
            }
            if ($passenger->status === 'cancelled') {
                DB::rollBack();
                return back()->with('error', 'Bu yo‘lovchi allaqachon bekor qilingan');
            }

            $price = $trip->price_per_seat;

            // Yo'lovchini bekor qilish va o'rinlarni qaytarish
            $passenger->update(['status' => 'cancelled']);

            $trip->increment('available_seats');
            if ($trip->available_seats > 0) {
                $trip->update(['status' => 'active']);
            }

            $booking->decrement('seats_booked');
            $booking->decrement('total_price', $price);
            if ($booking->seats_booked <= 0) {
                $booking->update(['status' => 'cancelled']);
            }

            $startQuarterName = $trip->startQuarter?->name ?? '';
            $endQuarterName   = $trip->endQuarter?->name ?? '';

            $serviceFeePercent = config('services.fees.service_fee_for_compliting_order');
            if (!$serviceFeePercent) {
                $serviceFeePercent = 5;
            }
            $serviceFee = ($price * ($serviceFeePercent / 100));
            $return     = ($price - $serviceFee);

            // 💰 CLIENT refund (xizmat haqqi ushlab qolinadi)
            $client     = $booking->user;
            $clientLang = $client?->authLanguage?->language ?? 'uz';

            $clientBalance = UserBalance::where('user_id', $booking->user_id)
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => $booking->user_id], ['balance' => 0]);

            $reasonForClient = [
                'uz' => "Admin tomonidan buyurtmangizdan bir yo‘lovchi bekor qilindi. Yo‘nalish: {$startQuarterName} dan {$endQuarterName} ga. Balansingizga {$return} so‘m qaytarildi. Xizmat haqqi: {$serviceFee} so‘m ushlab qolindi.",
                'en' => "An administrator cancelled one passenger from your booking. Route: from {$startQuarterName} to {$endQuarterName}. {$return} UZS was refunded to your balance. Service fee withheld: {$serviceFee} UZS.",
                'ru' => "Администратор отменил одного пассажира из вашего бронирования. Маршрут: от {$startQuarterName} до {$endQuarterName}. На ваш баланс возвращено {$return} сум. Удержан сервисный сбор: {$serviceFee} сум.",
            ];

            $clientBefore = $clientBalance->balance;
            $clientBalance->balance = $clientBefore + $return;
            $clientBalance->save();

            BalanceTransaction::create([
                'user_id'        => $booking->user_id,
                'type'           => 'credit',
                'amount'         => $return,
                'balance_before' => $clientBefore,
                'balance_after'  => $clientBalance->balance,
                'trip_id'        => $trip->id,
                'reference_id'   => $booking->id,
                'status'         => 'success',
                'reason'         => $reasonForClient[$clientLang] ?? $reasonForClient['uz'],
            ]);

            // 💰 DRIVER (yechib olinadi)
            $driverLoss = $price - $serviceFee;
            $driverLang = $trip->driver?->authLanguage?->language ?? 'uz';

            $driverBalance = UserBalance::where('user_id', $trip->driver_id)
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => $trip->driver_id], ['balance' => 0]);

            $reasonForDriver = [
                'uz' => "Admin tomonidan buyurtmadan bir yo‘lovchi bekor qilindi. Yo‘nalish: {$startQuarterName} dan {$endQuarterName} ga. Balansingizdan {$driverLoss} so‘m yechib olindi.",
                'en' => "An administrator cancelled one passenger from the booking. Route: from {$startQuarterName} to {$endQuarterName}. {$driverLoss} UZS was deducted from your balance.",
                'ru' => "Администратор отменил одного пассажира из бронирования. Маршрут: от {$startQuarterName} до {$endQuarterName}. С вашего баланса списано {$driverLoss} сум.",
            ];

            $driverBefore = $driverBalance->balance;
            $driverBalance->balance = $driverBefore - $driverLoss;
            $driverBalance->save();

            BalanceTransaction::create([
                'user_id'        => $trip->driver_id,
                'type'           => 'debit',
                'amount'         => $driverLoss,
                'balance_before' => $driverBefore,
                'balance_after'  => $driverBalance->balance,
                'trip_id'        => $trip->id,
                'reference_id'   => $booking->id,
                'status'         => 'success',
                'reason'         => $reasonForDriver[$driverLang] ?? $reasonForDriver['uz'],
                'created_at'     => Carbon::now()->addMinutes(1),
            ]);

            DB::commit();

            return back()->with('success', "Yo‘lovchi bekor qilindi. Clientga {$return} so‘m qaytarildi, haydovchidan {$driverLoss} so‘m yechib olindi (xizmat haqqi: {$serviceFee} so‘m).");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Admin tomonidan haydovchi safarini bekor qilish.
     * Mantiq DriverTripRepository::cancel bilan bir xil — haydovchidan xizmat haqqi (soliq) ushlab qolinadi,
     * mijozlarga to'liq summa + kompensatsiya qaytariladi, hammasi balance_transactions ga yoziladi.
     * Faqat yondashuv: "Admin tomonidan bekor qilindi".
     */
    public function cancelTrip($tripId)
    {
        try {
            DB::beginTransaction();

            $trip = Trip::where('id', $tripId)->lockForUpdate()->first();
            if (!$trip) {
                DB::rollBack();
                return back()->with('error', 'Safar topilmadi');
            }
            if ($trip->status === 'cancelled') {
                DB::rollBack();
                return back()->with('error', 'Safar allaqachon bekor qilingan');
            }

            // Vaqt tekshiruvi: hozirgi vaqt safar boshlanish va tugash vaqti oralig'ida bo'lishi kerak
            $now   = Carbon::now();
            $start = Carbon::parse($trip->start_time);
            $end   = Carbon::parse($trip->end_time);
            if ($now->lt($start) || $now->gt($end)) {
                DB::rollBack();
                return back()->with('error', "Safarni faqat boshlanish va tugash vaqti oralig‘ida bekor qilish mumkin. Safar vaqti: {$start->format('d.m.Y H:i')} — {$end->format('d.m.Y H:i')}.");
            }

            // Safarni bekor qilish va arxivga yozish
            $trip->update(['status' => 'cancelled', 'expired_at' => now()]);

            DB::table('expired_trips')->insert([
                'driver_id'        => $trip->driver_id,
                'vehicle_id'       => $trip->vehicle_id,
                'start_point_id'   => $trip->start_point_id,
                'end_point_id'     => $trip->end_point_id,
                'start_quarter_id' => $trip->start_quarter_id,
                'end_quarter_id'   => $trip->end_quarter_id,
                'start_time'       => $trip->start_time,
                'end_time'         => $trip->end_time,
                'price_per_seat'   => $trip->price_per_seat,
                'total_seats'      => $trip->total_seats,
                'available_seats'  => $trip->available_seats,
                'status'           => 'cancelled',
                'expired_at'       => now(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Foizlar
            $companyPercent            = (float) env('SERVICE_FEE_FOR_DRIVERS_TO_CANCEL_TRIP', 4); // 4%
            $clientCompensationPercent = (float) env('REFOUND_COMPENSATION_FOR_CLIENTS', 1);       // 1%

            $driver = $trip->driver;
            $driverLang = $driver?->authLanguage?->language ?? 'uz';

            $companyBalance = CompanyBalance::lockForUpdate()->first();
            if (!$companyBalance) {
                $companyBalance = CompanyBalance::create(['balance' => 0]);
            }

            $driverBalance = UserBalance::where('user_id', $driver->id)
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => $driver->id], ['balance' => 0]);

            $startName = $trip->startQuarter->name ?? '';
            $endName   = $trip->endQuarter->name ?? '';

            foreach ($trip->bookings as $booking) {
                if ($booking->status === 'cancelled') {
                    continue;
                }

                $client = $booking->user;
                if (!$client) {
                    continue;
                }
                $clientLang = $client->authLanguage?->language ?? 'uz';
                $clientBalance = UserBalance::where('user_id', $client->id)
                    ->lockForUpdate()
                    ->firstOrCreate(['user_id' => $client->id], ['balance' => 0]);

                $totalPrice          = $booking->total_price;
                $clientCompensation  = $totalPrice * ($clientCompensationPercent / 100);
                $companyFee          = $totalPrice * ($companyPercent / 100);
                $overallCompensation = $clientCompensation + $companyFee;
                $driverDeductionOnDocs = $totalPrice + $clientCompensation + $companyFee;
                $driverGotBeforeCancel = $totalPrice - ($companyFee + $clientCompensation);

                // --- HAYDOVCHI: 1-debit (asosiy daromad teskari) ---
                $driverBefore = $driverBalance->balance;
                $driverAfter  = ($driverBefore + $companyFee + $clientCompensation) - $totalPrice;
                $amount       = $totalPrice - ($companyFee + $clientCompensation);

                $reasonDriver = [
                    'uz' => "Sizning safaringiz administrator tomonidan bekor qilindi. Bekor qilinishidan oldin olishingiz kerak bo‘lgan summa: {$driverGotBeforeCancel} so‘m. Umumiy tushum: {$totalPrice} so‘m edi. Mijozga sizning hisobingizdan {$clientCompensation} so‘m kompensatsiya berildi. Bekor qilingani uchun kompaniya {$companyFee} so‘m ushlab qoldi. Yakunda sizdan ushlab qolinadigan umumiy summa: {$driverDeductionOnDocs} so‘m.",
                    'ru' => "Ваша поездка была отменена администратором. Сумма до отмены: {$driverGotBeforeCancel} сум. Общий доход: {$totalPrice} сум. Клиенту с вашего счёта выплачена компенсация {$clientCompensation} сум. За отмену удержана комиссия компании: {$companyFee} сум. Итоговая сумма удержания: {$driverDeductionOnDocs} сум.",
                    'en' => "Your trip was cancelled by an administrator. Amount before cancellation: {$driverGotBeforeCancel} UZS. Total revenue: {$totalPrice} UZS. A compensation of {$clientCompensation} UZS was paid to the client from your balance. A company fee of {$companyFee} UZS was withheld. Total deducted: {$driverDeductionOnDocs} UZS.",
                ];

                BalanceTransaction::create([
                    'user_id'        => $driver->id,
                    'type'           => 'debit',
                    'amount'         => $amount,
                    'balance_before' => $driverBefore,
                    'balance_after'  => $driverAfter,
                    'trip_id'        => $trip->id,
                    'reference_id'   => $booking->id,
                    'status'         => 'success',
                    'reason'         => $reasonDriver[$driverLang] ?? $reasonDriver['uz'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $driverBalance->update(['balance' => $driverAfter]);

                // --- HAYDOVCHI: 2-debit (kompaniya haqqi + kompensatsiya) ---
                $driverBefore = $driverBalance->balance;
                $driverAfter  = $driverBefore - $overallCompensation;

                $reasonCompensation = [
                    'uz' => "Safar admin tomonidan bekor qilingani uchun {$companyFee} so‘m kompaniya to‘lovi va {$clientCompensation} so‘m mijoz kompensatsiyasi hisobingizdan ushlab qolindi.",
                    'ru' => "За отмену поездки администратором с вашего счёта удержаны {$companyFee} сум комиссии компании и {$clientCompensation} сум компенсации клиенту.",
                    'en' => "Due to the admin cancellation, {$companyFee} UZS company fee and {$clientCompensation} UZS client compensation were deducted from your account.",
                ];

                BalanceTransaction::create([
                    'user_id'        => $driver->id,
                    'type'           => 'debit',
                    'amount'         => $overallCompensation,
                    'balance_before' => $driverBefore,
                    'balance_after'  => $driverAfter,
                    'trip_id'        => $trip->id,
                    'reference_id'   => $booking->id,
                    'status'         => 'success',
                    'reason'         => $reasonCompensation[$driverLang] ?? $reasonCompensation['uz'],
                    'created_at'     => now()->addMinutes(1),
                    'updated_at'     => now()->addMinutes(1),
                ]);
                $driverBalance->update(['balance' => $driverAfter]);

                // --- KOMPANIYA: chiqim (haydovchidan olingan foizlarni qaytarish bosqichi) ---
                $companyBefore = $companyBalance->balance;
                $companyAfter  = $companyBefore - ($companyFee + $clientCompensation);

                CompanyBalanceTransaction::create([
                    'company_balance_id' => $companyBalance->id,
                    'amount'             => ($companyFee + $clientCompensation),
                    'balance_before'     => $companyBefore,
                    'balance_after'      => $companyAfter,
                    'trip_id'            => $trip->id,
                    'booking_id'         => $booking->id,
                    'type'               => 'outgoing',
                    'status'             => 'success',
                    'reason'             => "Admin {$driver->first_name} ({$driver->phone}) safarini ({$startName} → {$endName}) bekor qildi. Kompaniya oldin olgan {$overallCompensation} so‘m qaytarildi.",
                    'currency'           => 'UZS',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
                $companyBalance->update(['balance' => $companyAfter]);

                // --- MIJOZ: to'liq summa + kompensatsiya qaytariladi ---
                $refundToClient = $totalPrice + $clientCompensation;
                $clientBefore   = $clientBalance->balance;
                $clientAfter    = $clientBefore + $refundToClient;

                $reasonClient = [
                    'uz' => "Safaringiz administrator tomonidan bekor qilindi. Sizga to‘liq summa ({$totalPrice} so‘m) va kompensatsiya ({$clientCompensation} so‘m) qaytarildi.",
                    'ru' => "Ваша поездка была отменена администратором. Вам возвращена полная сумма ({$totalPrice} сум) и компенсация ({$clientCompensation} сум).",
                    'en' => "Your trip was cancelled by an administrator. The full amount ({$totalPrice} UZS) and compensation ({$clientCompensation} UZS) have been refunded.",
                ];

                BalanceTransaction::create([
                    'user_id'        => $client->id,
                    'type'           => 'credit',
                    'amount'         => $refundToClient,
                    'balance_before' => $clientBefore,
                    'balance_after'  => $clientAfter,
                    'trip_id'        => $trip->id,
                    'reference_id'   => $booking->id,
                    'status'         => 'success',
                    'reason'         => $reasonClient[$clientLang] ?? $reasonClient['uz'],
                ]);
                $clientBalance->update(['balance' => $clientAfter]);

                // --- KOMPANIYA: kirim (ushlab qolingan xizmat haqqi) ---
                $companyBefore = $companyBalance->balance;
                $companyAfter  = $companyBefore + $companyFee;

                CompanyBalanceTransaction::create([
                    'company_balance_id' => $companyBalance->id,
                    'amount'             => $companyFee,
                    'balance_before'     => $companyBefore,
                    'balance_after'      => $companyAfter,
                    'trip_id'            => $trip->id,
                    'booking_id'         => $booking->id,
                    'type'               => 'income',
                    'status'             => 'success',
                    'reason'             => "Admin {$driver->first_name} ({$driver->phone}) safarini ({$startName} → {$endName}) bekor qildi. Kompaniyaga {$companyFee} so‘m xizmat haqqi qaytdi.",
                    'currency'           => 'UZS',
                    'created_at'         => now()->addMinutes(1),
                    'updated_at'         => now()->addMinutes(1),
                ]);
                $companyBalance->update(['balance' => $companyAfter]);

                // Booking va yo'lovchilarni bekor qilish
                BookingPassengers::where('booking_id', $booking->id)->update(['status' => 'cancelled']);
                $booking->update(['status' => 'cancelled']);
            }

            DB::commit();

            return back()->with('success', 'Safar admin tomonidan bekor qilindi. Mijozlarga to‘liq summa va kompensatsiya qaytarildi, haydovchidan xizmat haqqi ushlab qolindi.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function deleteDriver($id)
    {
        $userClientOrDriver = User::where('id', $id)->where('role', 'driver')->first();

        if ($userClientOrDriver) {
            $userClientOrDriver->delete();
            return redirect()->route('welcome')->with('success', 'Foydalanuvchi muvaffaqiyatli o‘chirildi!');

        }
        return redirect()->route('welcome')->with('success', 'Foydalanuvchi muvaffaqiyatli o‘chirildi!');
    }
}
