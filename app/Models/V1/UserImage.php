<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    protected $table = 'user_images';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'image_path',
        'type',
        'side',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
