<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\VehicleService;
use App\Http\Requests\V1\StoreRequest;
use App\Http\Requests\V1\UpdateRequest;
use App\Http\Resources\V1\VehicleResource;
use App\Models\V1\Vehicle;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public $errorResponse = [
        'status' => 'error',
        'message' => 'Vehicle not found'
    ];
    protected $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function index()
    {
        return VehicleResource::collection($this->vehicleService->getAll());
    }

    public function store(StoreRequest $request)
    {
        return $this->vehicleService->create($request->validated());
    }

    public function show($id)
    {
        return $this->vehicleService->findById($id);
    }

    public function update(UpdateRequest $request, $id)
    {
        return $this->vehicleService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->vehicleService->delete($id);
    }



}
