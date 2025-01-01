<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\V1\Balance;
use App\Models\V1\Booking;
use App\Models\V1\Trip;
use App\Models\V1\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'password', 'image', 'region', 'district', 'village', 'home', 'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static function booted()
    {
        static::created(function ($user) {
            // Foydalanuvchi ro'yxatdan o'tgan paytda faqat balans yo'q bo'lsa qo'shish
            if (!$user->balance) {
                Balance::create([
                    'user_id' => $user->id,
                    'balance' => 100.00,  // Dastlabki balans
                ]);
            }
        });
    }

    public function balance()
    {
        return $this->hasOne(Balance::class);
    }


    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function driverTrips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function driverVehicles()
    {
        return $this->hasMany(Vehicle::class, 'user_id');
    }

    public function driverBookings()
    {
        return $this->hasManyThrough(Booking::class, Trip::class, 'driver_id', 'trip_id');
    }

    public function userBookings()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function userTrips()
    {
        return $this->hasManyThrough(Trip::class, Booking::class, 'user_id', 'id');
    }

    public function userVehicles()
    {
        return $this->hasManyThrough(Vehicle::class, Booking::class, 'user_id', 'id');
    }

    public function userDriverTrips()
    {
        return $this->hasManyThrough(Trip::class, Booking::class, 'user_id', 'driver_id');
    }

    public function userDriverVehicles()
    {
        return $this->hasManyThrough(Vehicle::class, Booking::class, 'user_id', 'driver_id');
    }


    

}
