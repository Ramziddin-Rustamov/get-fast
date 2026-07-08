<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Models\V1\Trip;

class Parcel extends Model
{

    protected $fillable = [
        'trip_id',
        'max_weight',
        'price_per_kg',
        'is_active',
        'max_length',
        'max_width',
        'max_height',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function bookings()
    {
        return $this->hasMany(ParcelBooking::class);
    }

    public function types()
    {
        return $this->belongsToMany(ParcelType::class, 'parcel_parcel_type');
    }
}
