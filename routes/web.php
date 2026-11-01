<?php

use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Http\Controllers\Stripe\StripeConnectController;
use App\Http\Controllers\Stripe\StripeProductController;
use App\Http\Controllers\Stripe\StripeSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('stripe/connect/mobile/return', [StripeConnectController::class, 'mobileReturn'])
    ->name('stripe.connect.mobile.return');
Route::get('stripe/connect/mobile/refresh', [StripeConnectController::class, 'mobileRefresh'])
    ->name('stripe.connect.mobile.refresh');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('token', function () {
        $user = auth()->user();
        $token = auth('api')->claims(['name' => $user->name])->login($user);

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    });

    Route::name('stripe.')->prefix('stripe')->group(function () {

        Route::name('connect.')->prefix('connect')->group(function () {
            Route::get('/', [StripeConnectController::class, 'index'])->name('index');
            Route::post('/', [StripeConnectController::class, 'create'])->name('create');
            Route::get('/onboard', [StripeConnectController::class, 'onboard'])->name('onboard');
            Route::get('/return', [StripeConnectController::class, 'return'])->name('return');
            Route::get('/refresh', [StripeConnectController::class, 'refresh'])->name('refresh');
            Route::delete('/delete', [StripeConnectController::class, 'delete'])->name('delete');
        }); 
            

        /*
        |--------------------------------------------------------------------------
        | Product management
        |--------------------------------------------------------------------------
        */

        Route::post('/webhook/thin', [StripeWebhookController::class, 'thin'])->name('webhook.thin');

        Route::get('/products', [StripeProductController::class, 'index'])->name('products');

        Route::post('/products', [StripeProductController::class, 'store'])->name('products.store');

        /*
        |--------------------------------------------------------------------------
        | Connected-account subscription
        |--------------------------------------------------------------------------
        */

        Route::name('subscription.')->prefix('subscription')->group(function () {
            Route::post('/checkout', [StripeSubscriptionController::class, 'checkout'])->name('checkout');
            Route::get('/success', [StripeSubscriptionController::class, 'success'])->name('success');
            Route::get('/cancel', [StripeSubscriptionController::class, 'cancel'])->name('cancel');
            Route::get('/portal', [StripeSubscriptionController::class, 'portal'])->name('portal');
        });
    });
});

require __DIR__.'/settings.php';
