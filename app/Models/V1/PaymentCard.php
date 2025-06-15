<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaymentCard extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
