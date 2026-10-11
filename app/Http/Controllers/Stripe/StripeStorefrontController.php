<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Throwable;

class StripeStorefrontController extends Controller
{
    public function __construct(
        private StripeService $stripe
    ) {
    }

    public function index(string $accountId)
    {
        try {
            /*
             * DEMO ONLY:
             *
             * We are deliberately using the Stripe account ID in the URL:
             *
             * /store/acct_123
             *
             * In a real application use something like:
             *
             * /store/my-guitar-school
             *
             * and resolve that slug to the Stripe account internally.
             */

            $products = $this->stripe
                ->client()
                ->products
                ->all(
                    [
                        'limit' => 20,
                        'active' => true,
                        'expand' => ['data.default_price'],
                    ],
                    [
                        'stripe_account' => $accountId,
                    ]
                );

            return view('stripe.store', [
                'accountId' => $accountId,
                'products' => $products->data,
            ]);

        } catch (Throwable $e) {

            abort(
                500,
                'Unable to load this storefront: ' .
                $e->getMessage()
            );
        }
    }

    public function checkout(
        Request $request,
        string $accountId
    ) {
        $validated = $request->validate([
            'product_id' => ['required', 'string'],
        ]);

        try {
            /*
             * First retrieve the product from the CONNECTED ACCOUNT.
             *
             * Never trust a product ID submitted by the browser without
             * verifying that it actually belongs to this connected account.
             */
            $product = $this->stripe
                ->client()
                ->products
                ->retrieve(
                    $validated['product_id'],
                    [
                        'expand' => ['default_price'],
                    ],
                    [
                        'stripe_account' => $accountId,
                    ]
                );

            $price = $product->default_price;

            if (!$price || !$price->active) {
                return back()->with(
                    'error',
                    'This product is not available for purchase.'
                );
            }

            /*
             * Direct charge:
             *
             * Because stripe_account is supplied to Checkout, the charge
             * is created on the connected account.
             *
             * The application fee monetizes the transaction.
             */
            $session = $this->stripe
                ->client()
                ->checkout
                ->sessions
                ->create(
                    [
                        'line_items' => [
                            [
                                'price' => $price->id,
                                'quantity' => 1,
                            ],
                        ],

                        'payment_intent_data' => [
                            /*
                             * DEMO:
                             * $1.23 application fee.
                             *
                             * Replace this with your real fee calculation.
                             */
                            'application_fee_amount' => 123,
                        ],

                        'mode' => 'payment',

                        'success_url' =>
                            route('stripe.store', [
                                'accountId' => $accountId,
                            ]) .
                            '?payment=success',

                        'cancel_url' =>
                            route('stripe.store', [
                                'accountId' => $accountId,
                            ]),
                    ],
                    [
                        'stripe_account' => $accountId,
                    ]
                );

            /*
             * Hosted Checkout handles the payment UI.
             */
            return redirect()->away($session->url);

        } catch (Throwable $e) {

            return back()->with(
                'error',
                'Unable to create checkout: ' .
                $e->getMessage()
            );
        }
    }
}