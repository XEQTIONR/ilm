<?php

use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Http\Controllers\Stripe\StripeConnectController;
use App\Http\Controllers\Stripe\StripeProductController;
use App\Http\Controllers\Stripe\StripeSubscriptionController;
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

    Route::name('stripe.')->prefix('stripe')->group(function () {

        Route::group(['prefix' => 'connect'], function () {

            Route::get('/', [StripeConnectController::class, 'index'])->name('connect');

            Route::name('connect.')->group(function () {
                Route::post('/', [StripeConnectController::class, 'create'])->name('create');
                Route::get('/onboard', [StripeConnectController::class, 'onboard'])->name('onboard');
                Route::get('/return', [StripeConnectController::class, 'return'])->name('return');
                Route::get('/refresh', [StripeConnectController::class, 'refresh'])->name('refresh');
            });
            
        });

        Route::post('/webhook/thin', [StripeWebhookController::class, 'thin'])->name('webhook.thin');
    });

    /*
     |--------------------------------------------------------------------------
     | Product management
     |--------------------------------------------------------------------------
     */

     Route::get(
        '/stripe/products',
        [StripeProductController::class, 'index']
    )->name('stripe.products');

    Route::post(
        '/stripe/products',
        [StripeProductController::class, 'store']
    )->name('stripe.products.store');

    /*
     |--------------------------------------------------------------------------
     | Connected-account subscription
     |--------------------------------------------------------------------------
     */

     Route::post(
        '/stripe/subscription/checkout',
        [StripeSubscriptionController::class, 'checkout']
    )->name('stripe.subscription.checkout');

    Route::get(
        '/stripe/subscription/success',
        [StripeSubscriptionController::class, 'success']
    )->name('stripe.subscription.success');

    Route::get(
        '/stripe/subscription/cancel',
        [StripeSubscriptionController::class, 'cancel']
    )->name('stripe.subscription.cancel');

    Route::get(
        '/stripe/subscription/portal',
        [StripeSubscriptionController::class, 'portal']
    )->name('stripe.subscription.portal');
});


    // Route::get(
    //     '/stripe/connect',
    //     [StripeConnectController::class, 'index']
    // )->name('stripe.connect');
    
    // Route::post(
    //     '/stripe/connect',
    //     [StripeConnectController::class, 'create']
    // )->name('stripe.connect.create');

    // Route::post(
    //     '/stripe/webhook/thin',
    //     [StripeWebhookController::class, 'thin']
    // )->name('stripe.webhook.thin');

    // Route::get(
    //     '/stripe/connect/onboard',
    //     [StripeConnectController::class, 'onboard']
    // )->name('stripe.connect.onboard');

    // Route::get(
    //     '/stripe/connect/return',
    //     [StripeConnectController::class, 'return']
    // )->name('stripe.connect.return');

    // Route::get(
    //     '/stripe/connect/refresh',
    //     [StripeConnectController::class, 'refresh']
    // )->name('stripe.connect.refresh');

require __DIR__.'/settings.php';
