<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Payment_log extends Model
{
    protected $table = 'payment_logs';

    protected $fillable = [
        'user_id',
        'request_data',
        'response_data'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
