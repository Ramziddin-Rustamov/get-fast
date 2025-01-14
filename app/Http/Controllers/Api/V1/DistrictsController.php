<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DistrictResource;
use App\Models\V1\District;

class DistrictsController extends Controller
{
    public function index()
    {
        return DistrictResource::collection(District::all());
    }

    public function getRegion($id)
    {
        return  DistrictResource::collection(District::where('region_id', $id)->get());
    }
}
