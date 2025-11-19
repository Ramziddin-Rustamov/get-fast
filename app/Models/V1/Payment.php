<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    

    protected $table = 'payments';


    protected $fillable = [
        'user_id',
        'card_id',
        'amount',
        'status',
        'payment_method',
        'pay_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
