<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;

class VehicleImages extends Model
{
    protected $table = 'vehicle_images';
    protected $fillable = ['vehicle_id', 'image_path', 'type'];
    public $timestamps = false;
  
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    
    
}
