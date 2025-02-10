<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\V1\District;
use App\Models\V1\Quarter;

class RegionController extends Controller
{
    public function getDistricts($region_id)
    {
        $districts = District::where('region_id', $region_id)->get();
        return response()->json($districts);
    }

    public function getQuarters($district_id)
    {
        $quarters = Quarter::where('district_id', $district_id)->get();
        return response()->json($quarters);
    }
}
