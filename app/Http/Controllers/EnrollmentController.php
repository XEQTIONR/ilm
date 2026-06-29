<?php

namespace App\Http\Controllers;

use App\Http\Resources\EnrollmentResource;
use App\Models\CourseFormat;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();        

        $enrollments = $user->enrollments()->active()->with('format.course')->get();
        // return $enrollments;
        return EnrollmentResource::collection($enrollments);
    }

    public function store(Request $request, CourseFormat $format)
    {
        $validated = $request->validate([
            // 'course_format_id' => 'required|exists:course_formats,id',
            'start_at' => 'date',
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
        $enrollment->start_at = $validated['start_at'];
        $enrollment->end_at = new Carbon($validated['start_at'])->addDays(30);
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
