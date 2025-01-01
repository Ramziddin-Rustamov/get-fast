<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $table = 'balances';
    protected $fillable = ['user_id', 'balance', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addBalance($amount)
    {
        $this->balance += $amount;
        $this->save();
    }

    public function subtractBalance($amount)
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            $this->save();
        } else {
            // Handle insufficient balance
            throw new \Exception('Insufficient balance');
        }
    }
}