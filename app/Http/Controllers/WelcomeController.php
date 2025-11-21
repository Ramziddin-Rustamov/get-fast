<?php

namespace App\Http\Controllers;

use App\Models\V1\Region;
use App\Models\V1\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }
}
