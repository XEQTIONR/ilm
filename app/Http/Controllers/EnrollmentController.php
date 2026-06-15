<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    //

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_format_id' => 'required|exists:course_formats,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'price' => 'required|numeric|min:0',
            'length' => 'required|integer|min:30',
            'currency' => 'required|string|max:3|min:3',
            'status' => 'required|string',
            'schedule' => 'required|array|min:1',
            'schedule.*.day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'schedule.*.time' => 'required|numeric|min:0',
        ]);
    }
}
