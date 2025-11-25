<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V1\Booking;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $dateFilter = $request->get('date');

        $query = Booking::with(['trip.startQuarter', 'trip.endQuarter', 'user']);

        if ($status) {
            $query->where('status', $status);
        }

        // DATE FILTER
        if ($dateFilter == 'today') {
            $query->whereDate('created_at', now());
        }

        if ($dateFilter == 'week') {
            $query->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ]);
        }

        if ($dateFilter == 'last_week') {
            $query->whereBetween('created_at', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek(),
            ]);
        }

        $bookings = $query->latest()->paginate(20);

        return view('admin-views.orders.index', compact('bookings', 'status', 'dateFilter'));
    }


    public function show(Booking $order)
    {
        $order->load(['trip.startQuarter', 'trip.endQuarter', 'user', 'passengers']);
        return view('admin-views.orders.show', compact('order'));
    }
}
