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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty' => 'required|string',
            'category' => 'required|string',
            'length_type' => 'required|string',
            'age_groups' => 'required|array',
            'age_groups.*' => 'required|string',
            // 'image' => 'nullable|string',
            // 'video' => 'nullable|string',
        ]);

        $course = new Course($request->all());
        $user = $request->user();
        $user->courses()->save($course);

        return new CourseResource($course);
    }
}
