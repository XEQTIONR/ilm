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

Route::get('/feed', function() {
    return Course::all();
});


Route::middleware('auth:sanctum')->group(function () {

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

