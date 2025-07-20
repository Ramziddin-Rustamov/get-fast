<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['name_uz', 'name_en', 'name_ru'];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
