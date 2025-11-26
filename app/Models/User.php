<?php

namespace App\Models;


use App\Models\V1\Balance;
use App\Models\V1\Booking;
use App\Models\V1\Card;
use App\Models\V1\CreditCard;
use App\Models\V1\Region;
use App\Models\V1\District;
use App\Models\V1\Quarter;
use App\Models\V1\ParcelBooking;
use App\Models\V1\PaymentCard;
use App\Models\V1\Trip;
use App\Models\V1\UserImage;
use App\Models\V1\UserLanguage;
use App\Models\V1\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User  extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $table = 'users';
    public $timestamps = true;
    protected $fillable = [
        'first_name',
        'last_name',

        'email',
        'father_name',
        'phone',
        'image',
        'region_id',
        'district_id',
        'quarter_id',
        'home',
        'role',
        'password',
        'is_verified',
        'verification_code'
    ];

    protected $hidden = [
        'remember_token',
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function language()
    {
        return $this->hasOne(UserLanguage::class);
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

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function isDefaultCard()
    {
        return $this->hasOne(Card::class)->where('status', 'verified')->where('is_default', true);
    }

    public function activeCard()
    {
        return $this->hasOne(PaymentCard::class)->where('is_active', true);
    }

    // public function clientCard()
    // {
    //     return $this->hasOne(CreditCard::class)->where('is_active', true);
    // }

    public function profileImage()
    {
        return $this->hasOne(UserImage::class)->where('type', 'profile');
    }

    public function passportImage()
    {
        return $this->hasOne(UserImage::class)->where('type', 'passport');
    }

    public function myBalance()
    {
        return $this->hasOne(UserBalance::class);
    }

    public function balance()
    {
        return $this->hasOne(UserBalance::class);
    }

    public function balanceTransactions()
    {
        return $this->hasMany(BalanceTransaction::class);
    }


    public function images()
    {
        return $this->hasMany(UserImage::class, 'user_id');
    }

    public function authLanguage()
    {
        return $this->hasOne(UserLanguage::class, 'user_id', 'id');
    }
    

    
}
