<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Vehicle extends Model
{
    protected $table = 'vehicles';

    protected $fillable = [
        'user_id',
        'make',
        'model',
        'year',
        'license_plate',
        'seats',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
