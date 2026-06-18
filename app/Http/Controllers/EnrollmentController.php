<?php

namespace App\Http\Controllers;

use App\Http\Resources\EnrollmentResource;
use App\Models\CourseFormat;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    //

    public function store(Request $request, CourseFormat $format)
    {
        Log::info($request->all());
        $validated = $request->validate([
            // 'course_format_id' => 'required|exists:course_formats,id',
            //'start_date' => 'nullable|date',
            //'end_date' => 'nullable|date',
            // 'price' => 'required|numeric|min:0',
            // 'length' => 'required|integer|min:30',
            // 'currency' => 'required|string|max:3|min:3',
            // 'status' => 'required|string',
            'schedule' => 'required|array|min:1|in_array_keys:mon,tue,wed,thu,fri,sat,sun',
            // 'schedule.*.day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            // 'schedule.*.time' => 'required|numeric|min:0',
        ]);

        $enrollment = new Enrollment();
        // $enrollment->course_format_id = $validated['course_format_id'];
        $enrollment->start_date = now();
        $enrollment->end_date = now()->addDays(30);
        $enrollment->price = $format->price;
        $enrollment->currency = 'USD';
        $enrollment->status = 'active';
        $enrollment->length = $format->lesson_length;
        $enrollment->schedule = $validated['schedule'];
        $enrollment->format()->associate($format);
        $enrollment->user()->associate($request->user());
        $enrollment->save();

        return new EnrollmentResource($enrollment);
    }
}
