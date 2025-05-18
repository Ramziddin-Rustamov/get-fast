<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ParcelBooking extends Model
{

    public $fillable = ['client_id', 'parcel_id', 'trip_id', 'receiver_phone', 
    'weight', 'total_price', 'receiver_phone', 'parcel_description',
    'status', 'created_at', 'updated_at'];


    public function client()
    {
        return $this->belongsTo(User::class)->where('role', 'client');
    }

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
