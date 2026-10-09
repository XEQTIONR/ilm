<?php

namespace App\Services;

use InvalidArgumentException;
use Stripe\StripeClient;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $secret = config('services.stripe.secret');

        /*
         * Fail immediately with a useful message if the developer forgot
         * to configure the Stripe API key.
         */
        if (empty($secret)) {
            throw new InvalidArgumentException(
                'Stripe is not configured. Set STRIPE_SECRET_KEY in your .env file.'
            );
        }

        /*
         * All Stripe requests in this application go through this client.
         *
         * We deliberately DO NOT specify a Stripe API version here.
         * The installed SDK determines its default API version.
         */
        $this->stripe = new StripeClient($secret);
    }

    public function client(): StripeClient
    {
        return $this->stripe;
    }
}