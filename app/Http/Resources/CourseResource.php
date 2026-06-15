<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourseResource extends JsonResource
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
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'video' => $this->video ? Storage::disk('public')->url($this->video) : null,
            'difficulty' => $this->difficulty,
            'category' => $this->category,
            'length_type' => $this->length_type,
            'age_groups' => $this->age_groups,
            'is_published' => $this->is_published,
            'total_lessons' => $this->total_lessons,
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->user),
            ]),
            $this->mergeWhen($this->relationLoaded('formats'), [
                'formats' => CourseFormatResource::collection($this->formats),
            ]),
        ];
    }
}
