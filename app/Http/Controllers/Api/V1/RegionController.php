<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RegionResource;
use App\Models\V1\Region;

class RegionController extends Controller
{
    public function index()
    {
        return RegionResource::collection(Region::all());
    }
}
