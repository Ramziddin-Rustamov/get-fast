<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\V1\Booking;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompleteTripsCommand extends Command
{
    protected $signature = 'trips:complete';
    protected $description = 'End time o‘tgan trip va bookinglarni completed qilish';

    public function handle()
    {
        $now = Carbon::now();

        DB::beginTransaction();

        try {
            // 1️⃣ End time o‘tgan va active/full bo‘lgan triplar
            $tripIds = Trip::whereIn('status', ['active', 'full'])
                ->where('end_time', '<', $now)
                ->pluck('id');

            if ($tripIds->isEmpty()) {
                $this->info('Completed qilinadigan trip yo‘q');
                DB::commit();
                return;
            }

            // 2️⃣ Triplarni completed qilish
            Trip::whereIn('id', $tripIds)
                ->update(['status' => 'completed']);

            // 3️⃣ Bookinglarni completed qilish (cancelled bo‘lmaganlar)
            Booking::whereIn('trip_id', $tripIds)
                ->where('status', '!=', 'cancelled')
                ->update(['status' => 'completed']);

            DB::commit();

            $this->info('Trip va bookinglar muvaffaqiyatli completed qilindi');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
        }
    }
}
