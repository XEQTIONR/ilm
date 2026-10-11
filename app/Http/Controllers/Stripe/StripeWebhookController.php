<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripeService $stripe
    ) {
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();

        $signature = $request->header(
            'Stripe-Signature'
        );

        $secret = config(
            'services.stripe.webhook_secret'
        );

        if (!$secret) {
            return response()->json([
                'error' =>
                    'STRIPE_WEBHOOK_SECRET is not configured.',
            ], 500);
        }

        try {

            /*
             * Stripe signs webhook payloads.
             *
             * NEVER trust the JSON body without verifying its signature.
             */
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $secret
            );

        } catch (UnexpectedValueException $e) {

            return response()->json([
                'error' => 'Invalid webhook payload.',
            ], 400);

        } catch (SignatureVerificationException $e) {

            return response()->json([
                'error' => 'Invalid webhook signature.',
            ], 400);
        }

        /*
         * IMPORTANT:
         *
         * Webhooks can be delivered more than once.
         *
         * A production application should store event IDs and make
         * processing idempotent before changing access rights.
         */
        switch ($event->type) {

            /*
             * Subscription upgraded/downgraded.
             */
            case 'customer.subscription.updated':

                $subscription = $event->data->object;

                $subscriptionId = $subscription->id;

                /*
                 * Determine which product/price is active.
                 */
                $price =
                    $subscription
                        ->items
                        ->data[0]
                        ->price
                        ?? null;

                $priceId = $price?->id;

                $quantity =
                    $subscription
                        ->items
                        ->data[0]
                        ->quantity
                        ?? null;

                /*
                 * TODO:
                 *
                 * Find your local user using your mapping of the
                 * Stripe customer/account to your application user.
                 *
                 * Then grant/revoke access according to $priceId.
                 */
                Log::info(
                    'Stripe subscription updated.',
                    [
                        'subscription_id' =>
                            $subscriptionId,

                        'price_id' =>
                            $priceId,

                        'quantity' =>
                            $quantity,

                        'cancel_at_period_end' =>
                            $subscription
                                ->cancel_at_period_end,
                    ]
                );

                break;


            /*
             * Subscription canceled.
             */
            case 'customer.subscription.deleted':

                $subscription = $event->data->object;

                /*
                 * TODO:
                 *
                 * Find the local user associated with this
                 * subscription and revoke access.
                 */
                Log::info(
                    'Stripe subscription deleted.',
                    [
                        'subscription_id' =>
                            $subscription->id,
                    ]
                );

                break;


            /*
             * Invoice successfully paid.
             */
            case 'invoice.payment_succeeded':

                $invoice = $event->data->object;

                /*
                 * TODO:
                 *
                 * Mark the invoice/payment as successful and grant
                 * whatever access your application provides.
                 */
                Log::info(
                    'Stripe invoice paid.',
                    [
                        'invoice_id' =>
                            $invoice->id,
                    ]
                );

                break;


            /*
             * Customer's default payment method changed.
             */
            case 'customer.updated':

                $customer = $event->data->object;

                /*
                 * IMPORTANT:
                 *
                 * Treat this as billing information only.
                 * Do not use the customer's billing email as an
                 * authentication credential.
                 */
                Log::info(
                    'Stripe customer billing information updated.',
                    [
                        'customer_id' =>
                            $customer->id,
                    ]
                );

                break;


            default:

                /*
                 * It is normal to receive events that your application
                 * does not currently need.
                 */
                Log::debug(
                    'Unhandled Stripe webhook.',
                    [
                        'type' => $event->type,
                    ]
                );
        }

        /*
         * Tell Stripe we successfully received the event.
         */
        return response()->json([
            'received' => true,
        ]);
    }

    public function thin(Request $request)
    {
        $payload = $request->getContent();

        $signature = $request->header(
            'Stripe-Signature'
        );

        $secret = config(
            'services.stripe.webhook_secret'
        );

        if (!$secret) {
            return response()->json([
                'error' =>
                    'STRIPE_WEBHOOK_SECRET is not configured.',
            ], 500);
        }

        try {

            /*
            * V2 thin events are parsed differently from ordinary
            * snapshot webhook events.
            */
            $thinEvent = $this->stripe
                ->client()
                ->parseThinEvent(
                    $payload,
                    $signature,
                    $secret
                );

        } catch (\Throwable $e) {

            Log::warning(
                'Invalid Stripe V2 thin event.',
                [
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'error' => 'Invalid thin event.',
            ], 400);
        }

        /*
        * A thin event contains enough information to identify the event,
        * but we retrieve the full event from Stripe to understand exactly
        * what changed.
        */
        try {

            $event = $this->stripe
                ->client()
                ->v2
                ->core
                ->events
                ->retrieve($thinEvent->id);

        } catch (\Throwable $e) {

            Log::error(
                'Unable to retrieve Stripe V2 event.',
                [
                    'thin_event_id' => $thinEvent->id,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'error' => 'Unable to retrieve event.',
            ], 500);
        }

        switch ($event->type) {

            case 'v2.account[requirements].updated':

                /*
                * Requirements changed.
                *
                * TODO:
                *
                * Retrieve the associated account/resource as needed
                * and update whatever internal notification/state your
                * application requires.
                */
                Log::info(
                    'Stripe account requirements changed.',
                    [
                        'event_id' => $event->id,
                    ]
                );

                break;


            case 'v2.account[configuration.merchant].capability_status_updated':

                /*
                * Merchant card-payment capability changed.
                */
                Log::info(
                    'Stripe merchant capability changed.',
                    [
                        'event_id' => $event->id,
                    ]
                );

                break;


            case 'v2.account[configuration.customer].capability_status_updated':

                /*
                * Customer capability changed.
                */
                Log::info(
                    'Stripe customer capability changed.',
                    [
                        'event_id' => $event->id,
                    ]
                );

                break;


            default:

                Log::debug(
                    'Unhandled Stripe V2 thin event.',
                    [
                        'event_type' => $event->type,
                    ]
                );
        }

        return response()->json([
            'received' => true,
        ]);
    }
}