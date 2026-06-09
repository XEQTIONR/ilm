<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'difficulty' => $this->difficulty,
            'category' => $this->category,
            'length_type' => $this->length_type,
            'age_groups' => $this->age_groups,
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->user),
            ]),
        ];
    }
}
