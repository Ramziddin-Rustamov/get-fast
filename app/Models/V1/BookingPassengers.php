<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class BookingPassengers extends Model
{
    protected $table = 'booking_passengers';
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'name',
        'phone',
        'status',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

}
