<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
           return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->whenLoaded('user', fn() => $this->user->name),
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
            ],
            'course' => [
                'id' => $this->course_id,
                'title' => $this->whenLoaded('course', fn() => $this->course->title),
                'description' => $this->whenLoaded('course', fn() => $this->course->description),
                'level' => $this->whenLoaded('course', fn() => $this->course->level),
                'duration' => $this->whenLoaded('course', fn() => $this->course->duration),
                'rating' => $this->whenLoaded('course', fn() => $this->course->rating),
                'image_url' => $this->whenLoaded('course', fn() => $this->course->image_url),
                'available_seats' => $this->whenLoaded('course', fn() => $this->course->available_seats),
                'total_seats' => $this->whenLoaded('course', fn() => $this->course->total_seats),
            ],
            'booking_date' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
