<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Models\V1\Trip;
class Parcel extends Model
{

    protected $fillable = ['trip_id', 'max_weight', 'price_per_kg'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
