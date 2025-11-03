<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    

    protected $table = 'payments';


    protected $fillable = [
        'user_id',
        'booking_id',
        'amount',
        'status',
        'payment_method',
        'payment_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

}
