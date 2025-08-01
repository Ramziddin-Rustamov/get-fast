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

        'start_region_id',
        'end_region_id',

        'start_district_id',
        'end_district_id',
        'start_time',
        'end_time',
        'price_per_seat',
        'total_seats',
        'available_seats',
        'end_point_id',
        'start_point_id',
        'expired_at'
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

    public function startRegion()
    {
        return $this->belongsTo(Region::class, 'start_region_id');
    }

    public function endRegion()
    {
        return $this->belongsTo(Region::class, 'end_region_id');
    }


    public function startDistrict()
    {
        return $this->belongsTo(District::class, 'start_district_id');
    }

    public function endDistrict()
    {
        return $this->belongsTo(District::class, 'end_district_id');
    }

    public function parcels()
    {
        return $this->hasMany(Parcel::class);
    }

    public function scopeExpired($query)
    {
        return $query->where('end_time', '<', now())->where('status', '!=', 'canceled');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function startPoint()
    {
        return $this->belongsTo(Point::class, 'start_point_id');
    }

    public function endPoint()
    {
        return $this->belongsTo(Point::class, 'end_point_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function passengers()
    {
        return $this->hasManyThrough(
            \App\Models\V1\BookingPassengers::class,
            \App\Models\V1\Booking::class,
            'trip_id',         // Foreign key on bookings table
            'booking_id',      // Foreign key on booking_passengers table
            'id',              // Local key on trips table
            'id'               // Local key on bookings table
        );
    }
}
