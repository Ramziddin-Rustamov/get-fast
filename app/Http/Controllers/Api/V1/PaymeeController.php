<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\V1\Booking;
use App\Models\V1\Card;
use App\Models\V1\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\V1\BookingResource;

class PaymeeController extends Controller
{
    /**
     * Foydalanuvchining kartasini qo'shish va Payme'dan token olish
     */
    public function addCard(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string',
            'expiry' => 'required|string',
            'cvv' => 'required|string', // CVV kiriting
        ]);

        $card = new Card();
        $card->user_id = 1; // Foydalanuvchi ID sini aniqlang
        $card->card_number = substr($request->card_number, -4); // Faqat oxirgi 4 raqam
        $card->expiry = $request->expiry;

        // Payme API orqali token olish
        $response = Http::withHeaders(['X-Auth' => env('PAYME_SECRET_KEY'),
        ])->post(
            env('PAYME_ENDPOINT'),[
            'method' => 'cards.register',
            'params' => [
                'card_number' => $request->card_number,
                'expiry' => $request->expiry,
                'cvv' => $request->cvv,
            ],
        ]);

        if ($response->successful() && $response->json('result.state') === 1) {
            $card->token = $response->json('result.token');
            $card->save();
            return response()->json(['success' => true, 'message' => 'Card added and token saved']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to register card'], 500);
    }

    /**
     * Yo'lovchi safarini bron qilish
     */
    public function bookTrip(Request $data)
    {
        $trip =  Trip::where('id', $data['trip_id'])->where('available_seats', '>=', $data['seats_booked'])->first();
        if(is_null($trip) && empty($trip)){
            return response()->json(['message' => 'Trip not found or not enough seats available'], 404);
        }
        $tripDriver =  User::where('id', $trip->driver_id)->where('role', 'driver')->first();
        $client =  User::where('id', $data['user_id'])->where('role', 'client')->first();
        $admin = User::where('role', 'admin')->first();
 
         if(is_null($trip) && empty($trip)){
             return response()->json(['message' => 'Trip not found'] ,404);
         }
         if(is_null($client) && empty($client)){
             return response()->json(['message' => 'Client not found'], 404);
         }
 
         $trip->available_seats = $trip->available_seats - $data['seats_booked'];
 
        $driverAmount = $trip->price_per_seat * 0.90; // 50000 * 0.90 = 45000
        $driverAmount = $driverAmount * $data['seats_booked']; // 45000 * 2 = 90000
        $platformAmount = $trip->price_per_seat  * 0.10;

        $platformAmount = $platformAmount * $data['seats_booked']; // 50000 * 2 = 10000
        $tripDriver->balance->balance = $tripDriver->balance->balance + $driverAmount;
        $tripDriver->balance->save();
        $admin->balance->balance = $admin->balance->balance + $platformAmount;
        $admin->balance->save();
        // To'lovni PayMe API orqali yuborish
        // PayMe API uchun to'lov so'rovini yuboramiz
        $paymentResponse = $this->makePayment($data['payment_details']);

        if (!$paymentResponse['success']) {
        return response()->json(['message' => 'Payment failed', 'error' => $paymentResponse['error']], 400);
        }
 
         $booking = new Booking();
         $booking->trip_id = $data['trip_id'];
         $booking->user_id = $client->id;
         $booking->seats_booked = $data['seats_booked'];
         $booking->total_price = $trip->price_per_seat * $data['seats_booked'];
         $booking->status = "pending"; //// To'lov muvaffaqiyatli amalga oshirilgandan keyin statusni "confirmed" qilib o'zgartirishingiz mumkin
         $booking->save();
         $trip->save();
         
       

         return response()->json(new BookingResource($booking), 200);
    }


    /**
     * To'lovni amalga oshirish
     */

    public function makePayment($paymentDetails)
    {
       // PayMe API'ga to'lov yuborish (dummy API integration)
    // Bu yerda, siz PayMe API'ga so'rov yuborishingiz va muvaffaqiyatli to'lovni tekshirishingiz kerak bo'ladi
    // Masalan, to'lov muvaffaqiyatli amalga oshsa, $response['success'] = true bo'lishi kerak

    $response = Http::withHeaders([
        'X-Auth' => env('PAYME_SECRET_KEY')
    ])->post(env('PAYME_ENDPOINT'), [
        'method' => 'payment.process',
        'params' => [
            'card_number' => $paymentDetails['card_number'],
            'expiry' => $paymentDetails['expiry'],
            'cvv' => $paymentDetails['cvv'],
            'amount' => $paymentDetails['amount'], // To'lov miqdori
        ],
    ]);

    if ($response->successful() && $response->json('result.state') === 1) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => $response->json('error')];
    }       

   
}
