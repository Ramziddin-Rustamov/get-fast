<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class ParcelType extends Model
{
    protected $table = 'parcel_types';

    protected $fillable = [
        'name_uz',
        'name_ru',
        'name_en',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function parcels()
    {
        return $this->belongsToMany(Parcel::class, 'parcel_parcel_type');
    }
}
