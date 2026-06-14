<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CourseResource;

class CourseController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        return $user->courses;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|string|max:255',
            'video' => 'nullable|string|max:255',
            'difficulty' => 'required|string|in:beginner,intermediate,advanced',
            'category' => 'required|string|in:reading,qScience,hScience,calligraphy',
            'length_type' => 'required|string|in:fixed,ongoing',
            'age_groups' => 'required|array',
            'age_groups.*' => 'required|string|in:kids,teens,youngAdults,seniors',
            'is_published' => 'required|boolean',
            'formats' => 'required|array|min:1',
            'formats.*.title' => 'required|string|max:255',
            'formats.*.description' => 'required|string',
            'formats.*.unit' => 'required|string',
            'formats.*.lesson_length' => 'required|integer',
            'formats.*.lessons_per_week' => 'required|integer',
            'formats.*.price' => 'required|numeric|min:0',
            'formats.*.billing_cycles' => 'required|integer',
        ]);

        $course = new Course($validated);
        $user = $request->user();
        $user->courses()->save($course);

        $course->formats()->createMany($validated['formats']);
        $course->load('formats');
    
        return new CourseResource($course);
    }
}
