<?php

use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripeConnectController;
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

    Route::get(
        '/stripe/connect',
        [StripeConnectController::class, 'index']
    )->name('stripe.connect');
    
    Route::post(
        '/stripe/connect',
        [StripeConnectController::class, 'create']
    )->name('stripe.connect.create');

    Route::post(
        '/stripe/webhook/thin',
        [StripeWebhookController::class, 'thin']
    )->name('stripe.webhook.thin');
});

require __DIR__.'/settings.php';
