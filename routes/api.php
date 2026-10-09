<?php

use App\Http\Controllers\CourseAvailabilityController;
use App\Http\Controllers\CourseBannerController;    
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseVideoController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StripeConnectController;
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
    $token = $user->createToken($validated['device_name'])->plainTextToken;

    return  [
        'user' => new UserResource($user),
        'token' => $token,
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

    $token = $user->createToken($validated['device_name'])->plainTextToken;

    return  [
        'user' => $user,
        'token' => $token,
    ];
});

Route::get('/feed', function() {
    return CourseResource::collection(Course::with('instructor', 'formats')->get());
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

    Route::name('enrollments.')->prefix('enrollments')->group(function () {
        Route::get('/', [EnrollmentController::class, 'index'])->name('index');
        Route::post('/{format}', [EnrollmentController::class, 'store'])->name('store');
    });

    Route::name('courses.')->prefix('courses')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::post('/', [CourseController::class, 'store'])->name('store');
        Route::post('/{course}/banner', CourseBannerController::class)->name('banner.store');
        Route::post('/{course}/video', CourseVideoController::class)->name('video.store');

        Route::get('/{course}/availabilities', CourseAvailabilityController::class)->name('availabilities.index');

    });

    Route::name('uploads.')->prefix('uploads')->group(function () {
        Route::post('/', [UploadController::class, 'store'])->name('store');
        Route::delete('/{upload}', [UploadController::class, 'destroy'])->name('destroy');
    });

    

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

    Route::name('stripe.connect.')->prefix('stripe/connect')->group(function () {
        Route::get('/', [StripeConnectController::class, 'index'])->name('index');
        Route::post('/', [StripeConnectController::class, 'store'])->name('store');
        Route::get('/onboard', [StripeConnectController::class, 'onboard'])->name('onboard');
        Route::get('/return',[StripeConnectController::class, 'return'])->name('return');
        Route::get('/refresh',[StripeConnectController::class, 'refresh'])->name('refresh');
    });
});

