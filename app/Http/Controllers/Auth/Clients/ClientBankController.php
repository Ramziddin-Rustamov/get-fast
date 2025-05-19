<?php

namespace App\Http\Controllers\Auth\Clients;

use App\Http\Controllers\Controller;
use App\Models\V1\CreditCard;
use Illuminate\Http\Request;

class ClientBankController extends Controller
{
    public function index()
    {
        $cards = CreditCard::where('user_id', auth()->user()->id)->get();
        return view('auth.client.bank.index', compact('cards'));
    }
    public function store(Request $request)
    {
        // 1. Avval foydalanuvchining barcha kartalarini noaktiv qilish
        CreditCard::where('user_id', auth()->id())->update(['is_active' => false]);
    
        // 2. Yangi kartani yaratish va aktiv qilish
        $card = new CreditCard();
        $card->user_id = auth()->id();
        $card->card_number = $request->card_number;
        $card->expiry_month = $request->expiry_month;
        $card->expiry_year = $request->expiry_year;
        $card->cvv = $request->cvv;
        $card->is_active = true;
        $card->save();
    
        return redirect()->route('client.banks.index')->with('success', 'Karta muvaffaqiyatli qo‘shildi va aktiv qilindi.');
    }
    

    public function destroy($card)
    {
        $card = CreditCard::findOrFail($card);
        $card->delete();
        return redirect()->route('client.banks.index');
    }
}
