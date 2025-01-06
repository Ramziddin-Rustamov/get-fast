<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Quarter extends Model
{
    protected $fillable = ['name', 'district_id'];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
