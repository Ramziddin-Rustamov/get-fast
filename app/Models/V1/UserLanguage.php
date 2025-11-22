<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserLanguage extends Model
{
    protected $fillable = [
        'user_id',
        'language',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
