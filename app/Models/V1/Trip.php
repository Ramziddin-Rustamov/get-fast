<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Trip extends Model
{
    protected $table = 'trips';

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'start_location',
        'end_location',
        'start_time',
        'end_time',
        'price_per_seat',
        'total_seats',
        'available_seats',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }   

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
