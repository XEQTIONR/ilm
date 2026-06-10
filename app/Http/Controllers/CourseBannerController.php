<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;


class CourseBannerController extends Controller
{
    
    public function __invoke(Course $course, Request $request): string
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg' //|max:2048',
        ]);

        $course->image = $request->file('image')->store('images', 'public');
        $course->save();

        return $course->image;
    }
}
