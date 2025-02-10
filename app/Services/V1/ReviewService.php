<?php
namespace App\Services\V1;

use App\Repositories\V1\ReviewRepository;

class ReviewService
{
    private ReviewRepository $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function getAll()
    {
        return $this->reviewRepository->getAll();
    }

    public function find(int $id)
    {
        return $this->reviewRepository->find($id);
    }

    public function store(array $data)
    {
        return $this->reviewRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->reviewRepository->update($id, $data);
    }

    public function delete(int $id)
    {
        $this->reviewRepository->delete($id);
    }
}
