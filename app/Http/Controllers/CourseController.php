<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        return $user->courses;
    }
    
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // 'image' => 'nullable|string',
            // 'video' => 'nullable|string',
        ]);

        $course = Course::create($request->all());

        return $course;
    }
}
