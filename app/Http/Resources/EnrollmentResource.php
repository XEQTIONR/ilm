<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
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
            'start_date' => $this->start_date->toISOString(),
            'end_date' => $this->end_date->toISOString(),
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status,
            'length' => $this->length,
            'schedule' => $this->schedule,

            $this->mergeWhen($this->relationLoaded('format'), [
                'format' => new CourseFormatResource($this->format),
            ]),

            $this->mergeWhen($this->relationLoaded('course'), [
                'course' => new CourseResource($this->course),
            ]),
        ];
    }
}
