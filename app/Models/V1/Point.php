<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $table = 'points';

    public $timestamps = false;

    protected $fillable = [
        'lat',
        'long',
    ];

    public function startTrips()
    {
        return $this->hasMany(Trip::class, 'start_point_id');
    }

    public function endTrips()
    {
        return $this->hasMany(Trip::class, 'end_point_id');
    }

}
