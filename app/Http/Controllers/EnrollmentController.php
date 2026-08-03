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
    public function index(Request $request)
    {      
        return EnrollmentResource::collection(
            $request
                ->user()
                ->enrollments()
                ->active()
                ->with('format.course.instructor')
                ->get()
        );
    }

    public function store(Request $request, CourseFormat $format)
    {

        Log::info('all:', $request->all());

        $validated = $request->validate([
            'start_at' => 'date',
            'end_at' => 'nullable|date',
            'schedule' => 'required|array|min:1|in_array_keys:mon,tue,wed,thu,fri,sat,sun',
        ]);

        $user = $request->user();

        $enrollment = new Enrollment([
            'start_at' => $validated['start_at'],
            'end_at' => Arr::exists($validated, 'end_at') && $validated['end_at'] ? new Carbon($validated['end_at']) : null,
            'price' => $format->price,
            'currency' => 'USD',
            'status' => 'active',
            'length' => $format->lesson_length,
            'schedule' => $validated['schedule'],
            'timezone' => $user->timezone,  
        ]);

        $enrollment->format()->associate($format);
        $enrollment->user()->associate($user);
        
        $enrollment->save();
        $enrollment->load('format.course', 'user');

        return new EnrollmentResource($enrollment);
    }
}
