<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CoursesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url ? asset('storage/' . $this->image_url) : null,
            'level' => $this->level,
            'total_seats' => $this->total_seats,
            'available_seats' => $this->available_seats,
            'rating' => $this->rating,
            'duration' => $this->duration,
            'category' => $this->category ,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
