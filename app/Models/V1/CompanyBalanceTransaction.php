<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class CompanyBalanceTransaction extends Model
{
    protected $fillable = [
        'company_balance_id',
        'amount',
        'balance_before',
        'balance_after',
        'trip_id',
        'booking_id',
        'type',
        'reason',
        'currency'
    ];

    public function companyBalance()
    {
        return $this->belongsTo(CompanyBalance::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }


    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    
}
