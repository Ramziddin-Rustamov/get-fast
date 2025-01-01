<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\ReviewService;
use App\Http\Resources\V1\ReviewResource;
use App\Http\Requests\V1\ReviewStoreRequest;
use App\Http\Requests\V1\ReviewUpdateRequest;

class ReviewController extends Controller
{
    private ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index()
    {
        return ReviewResource::collection($this->reviewService->getAll());
    }

    public function store(ReviewStoreRequest $request)
    {
        return $this->reviewService->store($request->validated());
    }

    public function show(int $id)
    {
        return $this->reviewService->find($id);
    }

    public function update(ReviewUpdateRequest $request, int $id)
    {
        return  $this->reviewService->update($id, $request->validated());    
    }

    public function destroy(int $id)
    {
        return  $this->reviewService->delete($id);
    }
}
