<?php

use App\Http\Controllers\CourseController;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;


Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);
 
    $user = User::where('email', $request->email)->first();
 
    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
 
    return $user->createToken($request->device_name)->plainTextToken;
});

Route::post('/register', function(Request $request) {

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'gender' => 'required|string|max:6|in:male,female',
        'birthday' => 'required|date',
        'device_name' => 'required|string|max:255',
    ]);

    $user = User::create([
        ...$validated,
        'password' => Hash::make($validated['password']),
    ]);

    return $user->createToken($validated['device_name'])->plainTextToken;
});

Route::get('/feed', function() {
    return Course::all();
});


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', function(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    });

    Route::get('/jwt', function(Request $request) {
        $user = $request->user();
        $token = auth('api')->login($user);

        return $token;
    });


    Route::get('/courses', [CourseController::class, 'index']);


    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Route::get('/jwt', function(Request $request) {
    //     $user = $request->user();
    //     $token = auth('api')->claims(['name' => $user->name])->login($user);

    //     return [
    //         'user' => $user,
    //         'token' => $token
    //     ];
    // });
});

