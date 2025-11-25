<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\V1\Payment as V1Payment;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    public function index()
    {
        // Cache every 10 minutes → No heavy DB load
        $payments = Cache::remember('payments_list', 600, function () {
            return V1Payment::with(['user', 'card'])
                ->orderBy('id', 'desc')
                ->paginate(20);
        });

        return view('admin-views.payments.index', compact('payments'));
    }

    public function show($id)
    {
        $payment = V1Payment::with(['user', 'card'])->findOrFail($id);
        return view('admin-views.payments.show', compact('payment'));
    }
}
