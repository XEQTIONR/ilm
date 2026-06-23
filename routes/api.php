<?php

use App\Http\Controllers\CourseAvailabilityController;
use App\Http\Controllers\CourseBannerController;    
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseVideoController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UploadController;
use App\Http\Resources\CourseResource;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::post('/sanctum/token', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);
 
    $user = User::where('email', $validated['email'])->first();
 
    if (! $user || ! Hash::check($validated['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $user->load('instructor');
 
    return  [
        'user' => new UserResource($user),
        'token' => $user->createToken($validated['device_name'])->plainTextToken,
    ];
});

Route::post('/register', function(Request $request) {

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'gender' => 'required|string|max:6|in:male,female',
        'birthday' => 'required|date',
        'device_name' => 'required|string|max:255',
        'timezone' => 'required|timezone|max:255',
    ]);

    $user = User::create([
        ...$validated,
        'password' => Hash::make($validated['password']),
    ]);

    return  [
        'user' => $user,
        'token' => $user->createToken($validated['device_name'])->plainTextToken,
    ];
});

Route::get('/feed', function() {
    return CourseResource::collection(Course::with('formats')->get());
});

Route::get('/courses/{course}/availabilities', CourseAvailabilityController::class)->name('courses.availabilities.index');


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

    Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('/enrollments/{format}', [EnrollmentController::class, 'store'])->name('enrollments.store');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::post('/courses/{course}/banner', CourseBannerController::class)->name('courses.banner.store');
    Route::post('/courses/{course}/video', CourseVideoController::class)->name('courses.video.store');

    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
    Route::delete('/uploads/{upload}', [UploadController::class, 'destroy'])->name('uploads.destroy');

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load([
            'instructor', 
            'enrollments' => function ($query) {
                $query->active();
            }
        ]);
        return new UserResource($user);
    });

    Route::post('/instructors', [InstructorController::class, 'store']);
});

