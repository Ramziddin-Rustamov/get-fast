<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\V1\Trip;
use Illuminate\Support\Facades\DB;

class MoveExpiredTrips extends Command
{
    protected $signature = 'trips:expire';
    protected $description = 'Move expired trips to expired_trips table every 20 minutes';

    public function handle()
    {
        $expiredTrips = Trip::where('end_time', '<', now())->get();

        foreach ($expiredTrips as $trip) {
            DB::table('expired_trips')->insert([
                'driver_id' => $trip->driver_id,
                'vehicle_id' => $trip->vehicle_id,
                'start_point_id' => $trip->start_point_id,
                'end_point_id' => $trip->end_point_id,
                'start_quarter_id' => $trip->start_quarter_id,
                'end_quarter_id' => $trip->end_quarter_id,
                'start_time' => $trip->start_time,
                'end_time' => $trip->end_time,
                'price_per_seat' => $trip->price_per_seat,
                'total_seats' => $trip->total_seats,
                'available_seats' => $trip->available_seats,
                'status' => $trip->status,
                'expired_at' => now(),
                'created_at' => $trip->created_at,
                'updated_at' => $trip->updated_at,
            ]);
        }

        Trip::where('end_time', '<', now())->delete();

        $this->info('Expired trips moved successfully.');
    }
}
