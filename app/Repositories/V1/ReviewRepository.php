<?php

namespace App\Repositories\V1;

use App\Models\Review;
use App\Http\Resources\V1\ReviewResource;

class ReviewRepository
{
    public function getAll()
    {
        return Review::all();
    }

    public function find(int $id)
    {
       $review = Review::find($id);
       if(!$review){
           return response()->json(['message' => 'Review not found'], 404);
       }
       return response()->json(new ReviewResource($review), 200);
    }

    public function create(array $data)
    {
       $review = new Review();
       $review->trip_id = $data['trip_id'];
       $review->reviewer_id = $data['reviewer_id'];
       $review->reviewed_user_id = $data['reviewed_user_id'];
       $review->rating = $data['rating'];
       $review->comment = $data['comment'];
       $review->save();
       return response()->json(new ReviewResource($review), 200);
    }

    public function update(int $id, array $data)
    {
        $review = Review::find($id);
        if(is_null($review) && empty($review)){
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->update([
            'trip_id' => $data['trip_id'],
            'reviewer_id' => $data['reviewer_id'],
            'reviewed_user_id' => $data['reviewed_user_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
        return response()->json(new ReviewResource($review), 200);
    }

    public function delete(int $id)
    {
        $review = Review::find($id);
        if(!$review){
            return response()->json(['message' => 'Review not found'], 404);
        }
        $review->delete();
        return response()->json(['message' => 'Review deleted successfully'], 200);
    }
}
