<?php

namespace App\Http\Controllers;

use App\Models\V1\DriverPayment;
use App\Models\V1\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverPaymentController extends Controller
{
    public function index()
    {
        $payments = DriverPayment::with(['admin', 'driver'])->latest()->paginate(10);
        return view('driver_payments.index', compact('payments'));
    }



    public function destroy($id)
    {
        DriverPayment::findOrFail($id)->delete();
        return back()->with('success', 'To‘lov tarixi o‘chirildi!');
    }
}
