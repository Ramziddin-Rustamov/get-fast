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
        'start_quarter_id',
        'end_quarter_id',
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
}
