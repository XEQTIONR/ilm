<?php

namespace App\Http\Controllers;

use App\Http\Resources\EnrollmentResource;
use App\Models\CourseFormat;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();        

        $enrollments = $user->enrollments()->active()->with('format.course.instructor')->get();
        // return $enrollments;
        return EnrollmentResource::collection($enrollments);
    }

    public function store(Request $request, CourseFormat $format)
    {

        Log::info('all:', $request->all());

        $validated = $request->validate([
            // 'course_format_id' => 'required|exists:course_formats,id',
            'start_at' => 'date',
            'end_at' => 'nullable|date',
            // 'price' => 'required|numeric|min:0',
            // 'length' => 'required|integer|min:30',
            // 'currency' => 'required|string|max:3|min:3',
            // 'status' => 'required|string',
            'schedule' => 'required|array|min:1|in_array_keys:mon,tue,wed,thu,fri,sat,sun',
            // 'schedule.*.day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            // 'schedule.*.time' => 'required|numeric|min:0',
        ]);

        $enrollment = new Enrollment();
        $enrollment->start_at = $validated['start_at'];
        $enrollment->end_at = Arr::exists($validated, 'end_at') && $validated['end_at'] ? new Carbon($validated['end_at']) : null;
        $enrollment->price = $format->price;
        $enrollment->currency = 'USD';
        $enrollment->status = 'active';
        $enrollment->length = $format->lesson_length;
        $enrollment->schedule = $validated['schedule'];
        $enrollment->format()->associate($format);
        $enrollment->user()->associate($request->user());
        
        $enrollment->save();
        $enrollment->load('format.course', 'user');

        return new EnrollmentResource($enrollment);
    }
}
