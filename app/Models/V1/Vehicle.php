<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Vehicle extends Model
{
    protected $table = 'vehicles';

    protected $fillable = [
        'user_id',
        'color_id',
        'model',
        'car_number',
        'tech_passport_number',
        'seats',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function images()
    {
        return $this->hasMany(VehicleImages::class);
    }

    public function techPassport()
    {
        return $this->hasOne(VehicleImages::class)->where('type', 'tech_passport');
    }
}
