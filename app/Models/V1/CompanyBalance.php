<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class CompanyBalance extends Model
{


    protected $fillable = [
        'balance',
        'total_income'
    ];


    public function transactions()
    {
        return $this->hasMany(CompanyBalanceTransaction::class);
    }
}
