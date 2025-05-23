<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\V1\Balance;
use App\Models\V1\Booking;
use App\Models\V1\Region;
use App\Models\V1\District;
use App\Models\V1\Quarter;
use App\Models\V1\ParcelBooking;
use App\Models\V1\Trip;
use App\Models\V1\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\V1\CreditCard;
class User extends Authenticatable
{
    use HasFactory;
    protected $table = 'users';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'phone',
        'image',
        'region_id',
        'district_id',
        'quarter_id',
        'home',
        'role',
        'password',
    ];

    protected $hidden = [
        'remember_token',
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function balance()
    {
        return $this->hasOne(Balance::class, 'user_id', 'id');
    }


    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function driverTrips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function myVehicle()
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

    public function clientCard()
    {
        return $this->hasOne(CreditCard::class)->where('is_active', true)->where('user_type', 'client');
    }

    public function driverCard()
    {
        return $this->hasOne(CreditCard::class)->where('is_active', true)->where('user_type', 'driver');
    }


    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function quarter()
    {
        return $this->belongsTo(Quarter::class);
    }
    // Review
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewed_id');
    }

    public function parcelBookings()
    {
        return $this->hasMany(ParcelBooking::class);
    }
}
