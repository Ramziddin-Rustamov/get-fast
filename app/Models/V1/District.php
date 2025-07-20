<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['name_uz', 'name_en', 'name_ru','region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function quarters()
    {
        return $this->hasMany(Quarter::class);
    }
}
