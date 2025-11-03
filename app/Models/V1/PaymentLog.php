<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{

    public $table = 'payment_logs';

    protected $fillable = [
        'request',
        'response',
    ];
}
