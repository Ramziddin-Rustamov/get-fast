<?php

namespace App\Console\Commands;
use App\Models\V1\Booking;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';
    protected $description = 'Cancel bookings that were not paid within 10 minutes';

    public function handle()
    {
        $expiredBookings = Booking::where('status', 'pending')
            ->where('expired_at', '<', Carbon::now())
            ->get();

        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking) {
                $trip = Trip::find($booking->trip_id);
                if ($trip) {
                    $trip->available_seats += $booking->seats_booked;
                    $trip->save();
                }

                $booking->status = 'pending';
                $booking->expired_at = null;
                $booking->save();
            });

            $this->info("Booking ID: {$booking->id} has been canceled. because it was not paid within 10 minutes.");
        }
    }
}
