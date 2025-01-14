<?php

namespace App\Console\Commands;

use App\Models\V1\Trip;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredTrips extends Command
{
    protected $signature = 'trips:delete-expired';
    protected $description = 'Delete trips that have expired';

    public function handle()
    {
        $expiredTrips = Trip::where('end_time', '<', Carbon::now())->update(
            ['status' => 'expired']
        );
        $this->info($expiredTrips . ' Expired trips have been updated to expired .');
    }
}
