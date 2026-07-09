<?php

namespace App\Models;

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
}
