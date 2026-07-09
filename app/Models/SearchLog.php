<?php

namespace App\Models;

use App\Models\V1\District;
use App\Models\V1\Quarter;
use App\Models\V1\Region;
use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $table = 'search_logs';

    protected $fillable = [
        'user_id',
        'start_region_id',
        'start_district_id',
        'start_quarter_id',
        'end_region_id',
        'end_district_id',
        'end_quarter_id',
        'start_location',
        'end_location',
        'departure_date',
        'is_round_trip',
        'return_date',
        'results_count',
        'ip_address',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'return_date'    => 'datetime',
        'is_round_trip'  => 'boolean',
        'results_count'  => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- Qayerdan (start) ---
    public function startRegion()
    {
        return $this->belongsTo(Region::class, 'start_region_id');
    }

    public function startDistrict()
    {
        return $this->belongsTo(District::class, 'start_district_id');
    }

    public function startQuarter()
    {
        return $this->belongsTo(Quarter::class, 'start_quarter_id');
    }

    // --- Qayerga (end) ---
    public function endRegion()
    {
        return $this->belongsTo(Region::class, 'end_region_id');
    }

    public function endDistrict()
    {
        return $this->belongsTo(District::class, 'end_district_id');
    }

    public function endQuarter()
    {
        return $this->belongsTo(Quarter::class, 'end_quarter_id');
    }
}
