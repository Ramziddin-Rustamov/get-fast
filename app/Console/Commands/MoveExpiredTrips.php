<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\V1\Trip;
use App\Models\V1\ExpiredTrip;

class MoveExpiredTrips extends Command
{
    protected $signature = 'trips:move-expired-canceled';
    protected $description = 'Move expired and canceled trips to the expired_trips table';

    public function handle()
    {
        $this->moveTrips(Trip::expired()->get(), 'expired');
        $this->moveTrips(Trip::canceled()->get(), 'canceled');

        $this->info('Expired and canceled trips moved successfully.');
    }

    private function moveTrips($trips, $type)
    {
        foreach ($trips as $trip) {
            $data = $trip->toArray();
            $data['moved_type'] = $type; // expired yoki canceled deb belgilaymiz

            ExpiredTrip::create($data);
            $trip->delete();
        }
    }
}
