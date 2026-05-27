<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('token', function () {
        $user = auth()->user();
        $token = auth('api')->claims(['name' => $user->name])->login($user);
        // $token = auth('api')->jwtToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
        // return auth('api')->user();
        // return 'token';
        // return response()->json([
        //     'token' => 
        // ]);
    });
});

require __DIR__.'/settings.php';
