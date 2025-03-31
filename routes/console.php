<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


use App\Console\Commands\SendEmailsCommand;
use Illuminate\Support\Facades\Schedule;
 
Schedule::command('bookings:cancel-expired')->everyFiveMinutes();
Schedule::command('trips:move-expired-canceled')->everyFiveMinutes(); // moving expired Trips table 

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


