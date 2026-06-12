<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Availability;
class AvailabilityController extends Controller
{
    //

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'monday' => 'nullable|array',
            'tuesday' => 'nullable|array',
            'wednesday' => 'nullable|array',
            'thursday' => 'nullable|array',
            'friday' => 'nullable|array',
            'saturday' => 'nullable|array',
            'sunday' => 'nullable|array',
            'exceptions' => 'nullable|array',
            'meta' => 'nullable|array',
        ]);

        $availability = Availability::create($validated);
        return response()->json($availability);
    }
}
