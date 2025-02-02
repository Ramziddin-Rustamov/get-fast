<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions'; 
    protected $fillable = ['paycom_transaction_id', 'paycom_time', 'amount', 'state', 'booking_id']; 

    // Booking bilan bog'lanish
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
