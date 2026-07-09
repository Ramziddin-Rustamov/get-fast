<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchLog;
use Illuminate\Http\Request;

class SearchLogController extends Controller
{
    /**
     * Foydalanuvchilar qidiruvlari ro'yxati (marketing uchun).
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $logs = SearchLog::with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('start_location', 'like', "%{$search}%")
                        ->orWhere('end_location', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Umumiy statistika
        $stats = [
            'total'       => SearchLog::count(),
            'today'       => SearchLog::whereDate('created_at', now()->toDateString())->count(),
            'registered'  => SearchLog::whereNotNull('user_id')->count(),
        ];

        return view('admin-views.search-logs.index', compact('logs', 'stats', 'search'));
    }
}
