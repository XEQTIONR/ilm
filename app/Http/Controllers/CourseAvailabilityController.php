<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Availability;
use App\Http\Resources\AvailabilityResource;
use Illuminate\Database\Query\Builder;
class CourseAvailabilityController extends Controller
{   
    /**
     * Handle the incoming request.
     */
    public function __invoke(Course $course)
    {
        return AvailabilityResource::collection(
            Availability::with('instructor')
                ->where('instructor_id', $course->owned_by_id)
                ->whereDate('start_at', '<=', now())
                ->where(function ($query) {
                    $query->whereDate('end_at', '>=', now())
                        ->orWhere(function($query) {
                            $query->whereNull('end_at');
                        });
                })
                ->get()
        );
    }
}
