<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ParcelBooking extends Model
{
    protected $fillable = [
        'parcel_id',
        'trip_id',
        'parcel_type_id',
        'user_id',
        'receiver_phone',
        'parcel_description',
        'weight',
        'length',
        'width',
        'height',
        'total_price',
        'status',
        'expired_at',
    ];

    protected $casts = [
        'weight' => 'float',
        'total_price' => 'decimal:2',
        'expired_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function type()
    {
        return $this->belongsTo(ParcelType::class, 'parcel_type_id');
    }
}
