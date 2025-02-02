<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\V1\Booking;
use App\Models\V1\Card;
use App\Models\V1\Trip;
use App\Models\V1\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\V1\BookingResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class PaymeeController extends Controller
{
    /**
     * Foydalanuvchining kartasini qo'shish va Paymee'dan token olish
     */
    public function addCard(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string',
            'expiry' => 'required|string',
            'cvv' => 'required|string',
        ]);

        $guards = array_keys(config('auth.guards'));
        $user = null;

        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                $user = auth($guard)->user();
                break;
            }
        }
    
        // Paymee API orqali token olish
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('PAYMEE_SECRET_KEY'),
            'Content-Type'  => 'application/json',
        ])->post(env('PAYMEE_ENDPOINT') . '/cards/register', [
            'card_number' => $request->card_number,
            'expiry'      => $request->expiry,
            'cvv'         => $request->cvv,
        ]);
    
        // Agar token muvaffaqiyatli olingan bo'lsa, kartani bazaga qo'shamiz
        if ($response->successful() && isset($response->json()['data']['token'])) {
            Card::create([
                'user_id'     => $user->id,
                'card_number' => substr($request->card_number, -4), // Kartaning faqat oxirgi 4 raqami saqlanadi
                'expiry'      => $request->expiry,
                'token'       => $response->json()['data']['token'],
            ]);
    
            return response()->json(['success' => true, 'message' => 'Card added and token saved']);
        }
    
        return response()->json(['success' => false, 'message' => 'Failed to register card'], 500);
    }


    public function makePayment(Request $req)
    {
        if ($req->method == "CreateTransaction") {
            if (empty($req->params['account'])) {
                return response()->json([
                    'id' => $req->id,
                    'error' => [
                        'code' => -32504,
                        'message' => "Bajarish usuli uchun imtiyozlar etarli emas."
                    ]
                ]);
            }
    
            $account = $req->params['account'];
            $booking = Booking::find($account['booking_id'])->where('expired_at', '>', Carbon::now())->first();
    
            if (empty($booking)) {
                return response()->json([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31050,
                        'message' => [
                            "uz" => "Buyurtma topilmadi yoki 10 minutdan ko'p vaqt davomida tulov amalga oshmagani uchun qayta buyurtma qilishingiz mumkin",
                            "ru" => "Заказ не найден или платёжная сумма превышает 10 мин. истекает в течение 10 мин.",
                            "en" => "Order not found or payment amount exceeds 10 min. and expires within 10 min."
                        ]
                    ]
                ]);
            }
    
            if ($booking->total_price != $req->params['amount']) {
                return response()->json([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31001,
                        'message' => [
                            "uz" => "Notogri summa yuborildi",
                            "ru" => "Неверная сумма передана",
                            "en" => "Incorrect amount "
                        ]
                    ]
                ]);
            }
    
            $transaction = Transaction::where('booking_id', $account['booking_id'])->where('state', 1)->first();
    
            if (empty($transaction)) {
                $transaction = Transaction::create([
                    'paycom_transaction_id' => $req->params['id'],
                    'paycom_time' => $req->params['time'],
                    'paycom_time_datetime' => now(),
                    'amount' => $req->params['amount'],
                    'state' => 1,
                    'booking_id' => $account['booking_id'],
                ]);
    
                return response()->json([
                    "result" => [
                        'create_time' => $req->params['time'],
                        'transaction' => strval($transaction->id),
                        'state' => $transaction->state
                    ]
                ]);
            }
    
            if ($transaction->paycom_time == $req->params['time'] && $transaction->paycom_transaction_id == $req->params['id']) {
                return response()->json([
                    'result' => [
                        "create_time" => $req->params['time'],
                        "transaction" => "{$transaction->id}",
                        "state" => intval($transaction->state)
                    ]
                ]);
            }
    
            return response()->json([
                'id' => $req->id,
                'error' => [
                    'code' => -31099,
                    'message' => [
                        "uz" => "Buyurtma tolovi hozirda amalga oshrilmoqda",
                        "ru" => "Оплата заказа в данный момент обрабатывается",
                        "en" => "Order payment is currently being processed"
                    ]
                ]
            ]);
        }
    }

    public function checkPaymentStatus(Request $request)
    {
        // Validate the request
        $request->validate([
            'transaction_id' => 'required|string',
        ]);

        // Find the transaction by transaction_id
        $transaction = Transaction::where('paycom_transaction_id', $request->transaction_id)->first();

        if (empty($transaction)) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Return the transaction status
        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->paycom_transaction_id,
            'status' => $this->getStatusText($transaction->state),
            'amount' => $transaction->amount,
            'booking_id' => $transaction->booking_id,
        ]);
    }

    private function getStatusText($state)
    {
        switch ($state) {
            case 1:
                return 'Pending';
            case 2:
                return 'Completed';
            case -1:
                return 'Cancelled (Before Completion)';
            case -2:
                return 'Cancelled (After Completion)';
            default:
                return 'Unknown';
        }
    }



    public function bookTrip(Request $data)
    {
        $trip =  Trip::where('id', $data['trip_id'])->where('available_seats', '>=', $data['seats_booked'])->first();
        if(is_null($trip) && empty($trip)){
            return response()->json([
                'message' => 'Trip not found or not enough seats available',
                'status' => 'error'
            ], 404);
        }
        $client =  User::where('id', $data['user_id'])->where('role', 'client')->first();
 
         if(is_null($client) && empty($client)){
             return response()->json(['message' => 'Client not found'], 404);
         }

        
        // $driverAmount = $trip->price_per_seat * 0.90; 
        // $driverAmount = $driverAmount * $data['seats_booked'];
        // $platformAmount = $trip->price_per_seat  * 0.10;

        // $platformAmount = $platformAmount * $data['seats_booked'];
        // $tripDriver->balance->balance = $tripDriver->balance->balance + $driverAmount;
        // $tripDriver->balance->save();
        // $admin->balance->balance = $admin->balance->balance + $platformAmount;

         $booking = new Booking();
         $booking->trip_id = $data['trip_id'];
         $booking->user_id = $client->id;
         $booking->seats_booked = $data['seats_booked'];
         $booking->total_price = $trip->price_per_seat * $data['seats_booked'];
         $booking->status = "pending"; 
         $booking->expired_at = Carbon::now()->addMinutes(10); // 10 daqiqa vaqt beramiz
         $booking->save();
         
         $trip->available_seats = $trip->available_seats - $data['seats_booked'];
         $trip->save();
         return response()->json(new BookingResource($booking), 200);
    }
   
}
