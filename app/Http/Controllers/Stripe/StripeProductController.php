<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class StripeProductController extends Controller
{
    public function __construct(
        private StripeService $stripe
    ) {
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->stripe_account_id) {
            return redirect()
                ->route('stripe.connect.index')
                ->with('error', 'Connect your Stripe account first.');
        }

        /*
         * The Stripe-Account header tells Stripe that this Product
         * belongs to the connected account rather than the platform.
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
                    'stripe_account' => $user->stripe_account_id,
                ]
            );

        return view('stripe.products', [
            'products' => $products->data,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->stripe_account_id) {
            return back()->with(
                'error',
                'Connect your Stripe account first.'
            );
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0.50'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        try {
            /*
             * Convert dollars/euros/etc. to the smallest currency unit.
             *
             * Example:
             *
             * $19.99 -> 1999
             */
            $priceInCents = (int) round(
                ((float) $validated['price']) * 100
            );

            /*
             * IMPORTANT:
             *
             * stripe_account causes this request to be made on behalf
             * of the connected account.
             */
            $product = $this->stripe
                ->client()
                ->products
                ->create(
                    [
                        'name' => $validated['name'],

                        'description' =>
                            $validated['description'] ?? null,

                        'default_price_data' => [
                            'unit_amount' => $priceInCents,
                            'currency' => strtolower(
                                $validated['currency']
                            ),
                        ],
                    ],
                    [
                        'stripe_account' =>
                            $user->stripe_account_id,
                    ]
                );

            return redirect()
                ->route('stripe.products')
                ->with(
                    'success',
                    "Product {$product->name} created."
                );

        } catch (Throwable $e) {

            return back()->with(
                'error',
                'Stripe could not create the product: ' .
                $e->getMessage()
            );
        }
    }
}