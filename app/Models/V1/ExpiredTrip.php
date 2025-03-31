<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ExpiredTrip extends Model
{
    protected $table = 'expired_trips';

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'start_quarter_id',
        'end_quarter_id',
        'start_time',
        'end_time',
        'price_per_seat',
        'total_seats',
        'available_seats',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id')->where('role', 'driver');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function startQuarter()
    {
        return $this->belongsTo(Quarter::class, 'start_quarter_id');
    }

    public function endQuarter()
    {
        return $this->belongsTo(Quarter::class, 'end_quarter_id');
    }

    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'trip_id');
    }
}
