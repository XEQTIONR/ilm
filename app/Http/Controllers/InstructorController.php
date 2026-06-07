<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstructorController extends Controller
{
    //

    public function store(Request $request)
    {
        Log::info($request->all());
        $validated = $request->validate([
            'reading' => 'required|string|in:no,withHarakat,withoutHarakat|max:255',
            'writing' => 'required|string|in:no,yes|max:255',
            'speaking' => 'required|string|in:no,wordsOnly,MSA,multiple,native|max:255',
            'languages' => 'required|array',
            'titles' => 'nullable|array',
            'courseCategories' => 'required|array',
            
        ]);

        $validated['course_categories'] = $validated['courseCategories'];

        $instructor = $request->user()->instructor()->create($validated);

        return $instructor;
    }
}
