<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User as Client;
use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\V1\Card;
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;
use App\Services\V1\HamkorbankService;
use App\Services\V1\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ClientController extends Controller
{

    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }



    /**
     * Clients List
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $clients = Client::where('role', 'client')
            ->with(['balance', 'bookings'])
            ->when($search, function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            })
            ->when($status, function ($q) use ($status) {
                $q->where('is_verified', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin-views.clients.index', compact('clients', 'search', 'status'));
    }

    /**
     * Create client form
     */
    public function create()
    {
        return view('admin-views.clients.create');
    }

    /**
     * Store new client
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'nullable|string|max:255',
            'phone'        => 'required|unique:users,phone',
            'password'     => 'required|min:6',
        ]);

        $client = Client::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'role'       => 'client',
            'is_verified' => true,
            'verification_status' => 'approved',
        ]);

        return redirect()->route('clients.index')->with('success', 'Client muvaffaqiyatli qo‘shildi!');
    }

    /**
     * Show client details
     */
    public function show($client)
    {

        $client = Client::where('role', 'client')
            ->with(['balance', 'bookings'])
            ->find($client);

        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Mijoz topilmadi!');
        }

        $trips = $client->bookings()->orderBy('id', 'desc')->paginate(5);

        // Paginate qilingan buyurtmalar
        $bookings = $client->bookings()->orderBy('id', 'desc')->paginate(4);

        // Paginate qilingan tranzaksiyalar
        $balanceTransactions = $client->balanceTransactions()->orderBy('created_at', 'desc')->paginate(5);


        return view('admin-views.clients.show', compact(
            'client',
            'trips',
            'balanceTransactions',
            'bookings'
        ));
    }

    /**
     * Edit page
     */
    public function edit($client)
    {
        $client = Client::where('role', 'client')->find($client);

        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Client topilmadi!');
        }

        return view('admin-views.clients.edit', compact('client'));
    }

    /**
     * Update client
     */
    public function update(Request $request, $client)
    {
        $client = Client::where('role', 'client')->find($client);
        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Client topilmadi!');
        }

        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'nullable|string',
            'phone'      => 'required|unique:users,phone,' . $client->id,
        ]);

        $client->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'phone'      => $request->phone,
        ]);

        return redirect()->route('clients.show', $client->id)->with('success', 'Client yangilandi!');
    }

    /**
     * Delete client
     */
    public function destroy($client)
    {
        $client = Client::where('role', 'client')->find($client);

        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'Client topilmadi!');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client o‘chirildi!');
    }

    /**
     * Client trips
     */
    public function trips($client)
    {
        $client = Client::where('role', 'client')->findOrFail($client);
        $trips = $client->bookings()->orderBy('id', 'desc')->paginate(10);

        return view('admin-views.clients.trips', compact('client', 'trips'));
    }

    /**
     * Client balance transactions
     */
    public function balance($client)
    {
        $client = Client::where('role', 'client')->with('balance')->findOrFail($client);
        $balanceTransactions = $client->balanceTransactions()->orderBy('created_at', 'desc')->paginate(10);

        return view('admin-views.clients.balance', compact('client', 'balanceTransactions'));
    }

    /**
     * Client images (agar ishlatilsa)
     */
    public function images($client)
    {
        $client = Client::where('role', 'client')->with('images')->findOrFail($client);

        return view('admin-views.clients.images', compact('client'));
    }

    /**
     * Clientga SMS yuborish
     */
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

    public function refund(Request $request, $clientId)
    {
        try {

            DB::beginTransaction();

            $request->validate([
                'card_id' => 'required|exists:cards,id',
                'amount' => 'required|integer',
            ]);

            $client = User::where('role', 'client')->find($clientId);
            $clientLanguage = $client->authLanguage->language ?? 'uz';

            if ($request->amount <= 0) {
                $messages = [
                    'uz' => 'Miqdor 0 dan katta bo\'lishi kerak',
                    'ru' => 'Сумма должна быть больше 0',
                    'en' => 'Amount must be greater than 0',
                ];
                return redirect()->back()->with('error', $messages[$clientLanguage]);
            }

            if ($request->amount > 200000) {
                $messages = [
                    'uz' => 'Miqdor 200000 dan kam bo\'lishi kerak',
                    'ru' => 'Сумма должна быть меньше 200000',
                    'en' => 'Amount must be less than 200000',
                ];
                return redirect()->back()->with('error', $messages[$clientLanguage]);
            }

            if ($request->amount < 1000) {
                $messages = [
                    'uz' => 'Miqdor 1000 dan katta bo\'lishi kerak',
                    'ru' => 'Сумма должна быть больше 1000',
                    'en' => 'Amount must be greater than 1000',
                ];
                return redirect()->back()->with('error', $messages[$clientLanguage]);
            }


            $card = Card::where('id', $request->card_id)
                ->where('user_id', $client->id)
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

                return redirect()->back()->with('error', $messages[$clientLanguage]);
            }

            // Payer_data majburiy, hatto test summasi uchun ham
            $payerData = [
                "surname"     => $cleint->last_name ?? 'Test',
                "first_name"  => $cleint->first_name ?? 'Test',
                "middle_name" => $cleint->father_name ?? 'Test',
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


            $userBalanceBefore = $client->balance->balance;
            $client->balance->update([
                'balance' => $client->balance->balance - ($amountInKopeyka / 100),
            ]);

            // DB-ga yozish uchun misol
            BalanceTransaction::create([
                'user_id'    => $client->id,
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
                'uz' => "Pul muvaffaqiyatli qaytarildi. Karta: {$card->number}, summa: {$formattedAmount} so'm" . $client->first_name . "va" . "telefon raqami" . " " . $client->phone,
                'ru' => "Средства успешно возвращены. Карта: {$card->number}, сумма: {$formattedAmount} сум" . $client->first_name . "va" . "telefon raqami" . " " . $client->phone,
                'en' => "Refund successful. Card: {$card->number}, amount: {$formattedAmount} UZS" . $client->first_name . "va" . "telefon raqami" . " " . $client->phone,
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
            $this->smsService->sendQueued($client->phone, $refundMessage[$driverLanguage ?? 'uz'], 'refund-client-by-admins');

            $messages = [
                'uz' => 'Pul muvaffaqiyatli qaytarildi hisobingizdan chiqib ketdi',
                'ru' => 'Средства успешно возвращены с вашего счета',
                'en' => 'Refund successful from your account',
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

            return redirect()->back()->with('success', $messages[$clientLanguage ?? 'uz']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
