<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class CourseVideoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Course $course, Request $request): string
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,wmv,flv,mpeg,mpg,m4v,3gp,3g2,mj2' //|max:2048',
        ]);

        $course->video = $request->file('video')->store('videos', 'public');
        $course->save();

        return $course->video;
    }
}
