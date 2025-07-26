<?php

namespace App\Models;

use App\Models\V1\Trip;
use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
    protected $table = 'balance_transactions';

    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'trip_id',
        'status',
        'reason',
        'reference_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
