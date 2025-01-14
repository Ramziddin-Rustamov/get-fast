<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\QuarterResource;
use App\Models\V1\Quarter;
use Illuminate\Http\Request;

class QuarterController extends Controller
{
    public function index()
    {
        return QuarterResource::collection(Quarter::all());
    }

    public function getVillagesByDistrict($id)
    {
        return  QuarterResource::collection(Quarter::where('district_id', $id)->get());
    }
}
