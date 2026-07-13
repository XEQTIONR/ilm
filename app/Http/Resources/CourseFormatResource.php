<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseFormatResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'unit' => $this->unit,
            'lesson_length' => $this->lesson_length,
            'lessons_per_week' => $this->lessons_per_week,
            'total_lessons' => $this->total_lessons,
            'price' => $this->price,
            'billing_cycles' => $this->billing_cycles,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            $this->mergeWhen($this->relationLoaded('course'), [
                'course' => new CourseResource($this->course),
            ]),
        ];
    }
}
