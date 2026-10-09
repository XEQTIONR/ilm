<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class StripeSubscriptionController extends Controller
{
    public function __construct(
        private StripeService $stripe
    ) {
    }

    /**
     * Create a hosted Checkout session for the connected account.
     */
    public function checkout()
    {
        $user = Auth::user();

        if (!$user->stripe_account_id) {
            return redirect()
                ->route('stripe.connect')
                ->with(
                    'error',
                    'Connect your Stripe account first.'
                );
        }

        $priceId = config(
            'services.stripe.platform_price_id'
        );

        if (!$priceId) {
            return back()->with(
                'error',
                'STRIPE_PLATFORM_PRICE_ID is not configured.'
            );
        }

        try {

            /*
             * With V2 accounts, the connected account ID can be used
             * as the customer_account.
             */
            $session = $this->stripe
                ->client()
                ->checkout
                ->sessions
                ->create([
                    /*
                     * IMPORTANT:
                     *
                     * This is the CONNECTED ACCOUNT, not a Stripe
                     * Customer ID.
                     */
                    'customer_account' =>
                        $user->stripe_account_id,

                    'mode' => 'subscription',

                    'line_items' => [
                        [
                            'price' => $priceId,
                            'quantity' => 1,
                        ],
                    ],

                    'success_url' =>
                        route(
                            'stripe.subscription.success'
                        ) .
                        '?session_id={CHECKOUT_SESSION_ID}',

                    'cancel_url' =>
                        route(
                            'stripe.subscription.cancel'
                        ),
                ]);

            return redirect()->away($session->url);

        } catch (Throwable $e) {

            return back()->with(
                'error',
                'Unable to start subscription: ' .
                $e->getMessage()
            );
        }
    }

    public function success()
    {
        return view('stripe.subscription-success');
    }

    public function cancel()
    {
        return view('stripe.subscription-cancel');
    }

    /**
     * Create a Stripe Billing Portal session.
     */
    public function portal()
    {
        $user = Auth::user();

        if (!$user->stripe_account_id) {
            return redirect()
                ->route('stripe.connect');
        }

        try {

            $session = $this->stripe
                ->client()
                ->billingPortal
                ->sessions
                ->create([
                    'customer_account' =>
                        $user->stripe_account_id,

                    'return_url' =>
                        route('stripe.connect'),
                ]);

            return redirect()->away($session->url);

        } catch (Throwable $e) {

            return back()->with(
                'error',
                'Unable to open the billing portal: ' .
                $e->getMessage()
            );
        }
    }
}