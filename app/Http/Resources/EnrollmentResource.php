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
            'start_at' => $this->start_at->toISOString(),
            'end_at' => $this->end_at ? $this->end_at->toISOString() : null,
            'status' => $this->status,
            'length' => $this->length,
            'schedule' => $this->schedule,
            'timezone' => $this->timezone,
            
            $this->mergeWhen($this->relationLoaded('format'), [
                'format' => new CourseFormatResource($this->format),
            ]),

            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->user),
            ]),
        ];
    }
}
