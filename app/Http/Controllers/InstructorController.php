<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use Illuminate\Support\Arr;
use Carbon\Carbon;

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
            'course_categories' => 'required|array',
            'availability' => 'required|array',
            'timezone' => 'required|string|timezone',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date',
        ]);

        $instructor = $request->user()->instructor()->create($validated);

        foreach ($validated['availability'] as $key => $value) {
            $validated['availability'][$key] = [...Arr::sort($value)];
        }
        
        $availability = $validated['availability'];
        $availability['timezone'] = $validated['timezone'];
        $availability['start_at'] = Carbon::parse($validated['start_at']);
        $availability['end_at'] = $validated['end_at'] ? Carbon::parse($validated['end_at']) : null;

        $instructor->availabilities()->create($availability);

        $user = $request->user();
        $user->load('instructor');

        return new UserResource($user);
    }
}
