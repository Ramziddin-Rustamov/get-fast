<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = [
        'title_uz',
        'title_en',
        'title_ru',
        'code',
    ];
}
