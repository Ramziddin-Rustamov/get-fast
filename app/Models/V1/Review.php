<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['trip_id', 'reviewer_id', 'reviewed_user_id', 'rating', 'comment'];
}
