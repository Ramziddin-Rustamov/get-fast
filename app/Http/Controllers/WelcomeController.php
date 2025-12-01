<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\V1\Booking;
use App\Models\V1\Card;
use App\Models\V1\CompanyBalance;
use App\Models\V1\CompanyBalanceTransaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WelcomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    // === COMPANY DASHBOARD ===
    public function companyDashboard()
    {
        // CACHE 10 MINUTES
        $stats = Cache::remember('company_dashboard_stats', 600, function () {

            return [
                'company' => CompanyBalance::firstOrCreate(),

                // Bookings count
                'totalBookings' => Booking::count(),
                'confirmedBookings' => Booking::where('status', 'confirmed')->count(),
                'cancelledBookings' => Booking::where('status', 'cancelled')->count(),
                'completedBookings' => Booking::where('status', 'completed')->count(),

                // Users
                'totalClients' => User::where('role', 'client')->count(),
                'totalDrivers' => User::where('role', 'driver')->count(),

                // Driver Verification Status
                'driversApproved' => User::where('role', 'driver')
                    ->where('driving_verification_status', 'approved')->count(),

                'driversRejected' => User::where('role', 'driver')
                    ->where('driving_verification_status', 'rejected')->count(),

                'driversPending' => User::where('role', 'driver')
                    ->where('driving_verification_status', 'pending')->count(),

                'driversBlocked' => User::where('role', 'driver')
                    ->where('driving_verification_status', 'blocked')->count(),

                // Active / Inactive users (email verified)
                'activeUsers' => User::where('is_verified', true)->count(),
                'inactiveUsers' => User::where('is_verified', false)->count(),

                // Cards count
                'totalCards' => Card::count(),

                // Company transactions
                'todayIncome' => CompanyBalanceTransaction::whereDate('created_at', today())
                    ->sum('amount'),

                'totalTransactions' => CompanyBalanceTransaction::count(),
            ];
        });

        return view('admin-views.company.dashboard', $stats);
    }

    // === COMPANY TRANSACTIONS PAGE ===
    public function companyTransactions()
    {
        $transactions = CompanyBalanceTransaction::latest()->paginate(20);

        return view('admin-views.company.transactions', compact('transactions'));
    }
}
