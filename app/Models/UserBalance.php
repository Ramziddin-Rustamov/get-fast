<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{
    protected $table = 'user_balances';

    protected $fillable = [
        'user_id',
        'balance',
        'locked_balance',
        'currency',
        'tax',
        'after_taxes',
    ];
}
