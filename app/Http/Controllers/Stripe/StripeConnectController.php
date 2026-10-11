<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\V2\Core\Account as StripeAccount; 
use Throwable;

class StripeConnectController extends Controller
{
    public function __construct(
        private StripeService $stripe
    ) {
    }

    /**
     * Show creation form.
     */
        /**
     * Create the Stripe V2 connected account.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        /*
            * Don't create another Stripe account if the local user
            * has already been connected.
            */
        if ($user->stripe_account_id) {
            return redirect()
                ->route('stripe.connect.index')
                ->with('error', 'Your Stripe account already exists.');
        }

        try {
            Log::info("In try");
            /*
                * This is deliberately the V2 API.
                *
                * Notice that there is NO:
                *
                *     type => 'express'
                *     type => 'standard'
                *     type => 'custom'
                *
                * Those are not used here.
                */
            $account = $this->stripe->client()
                ->v2
                ->core
                ->accounts
                ->create([
                    'display_name' => $user->name,
                    'contact_email' => $user->email,

                    /*
                        * Demo assumption from the supplied requirements.
                        *
                        * In production this should come from the user's
                        * actual business country rather than being hard-coded.
                        */
                    'identity' => [
                        'country' => 'us',
                    ],

                    'dashboard' => 'full',

                    'defaults' => [
                        'responsibilities' => [
                            'fees_collector' => 'stripe',
                            'losses_collector' => 'stripe',
                        ],
                    ],

                    'configuration' => [
                        // 'customer' => [],

                        'merchant' => [
                            'capabilities' => [
                                'card_payments' => [
                                    'requested' => true,
                                ],
                            ],
                        ],
                    ],
                ]);

                Log::info("After account creation", [$account]);

            /*
                * Persist ONLY the relationship between our user and
                * Stripe's account ID.
                *
                * Onboarding status itself is intentionally NOT stored.
                */
            $user->stripe_account_id = $account->id;
            $user->save();

            return redirect()
                ->route('stripe.connect.index')
                ->with('success', 'Your Stripe account was created.');

        } catch (Throwable $e) {
            Log::info("In catch");
            Log::error('Stripe connected account creation failed.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Stripe could not create your account: ' . $e->getMessage()
            );
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $account = null;

        if ($user->stripe_account_id) {
            try {
                Log::info('Retrieving Stripe account.', [
                    'user_id' => $user->id,
                    'stripe_account_id' => $user->stripe_account_id,
                ]);
                $account = $this->stripe->client()
                    ->v2
                    ->core
                    ->accounts
                    ->retrieve(
                        $user->stripe_account_id,
                        [
                            'include' => [
                                'configuration.merchant',
                                'requirements',
                            ],
                        ]
                    );
            } catch (Throwable $e) {
                Log::error('Unable to retrieve Stripe account.', [
                    'user_id' => $user->id,
                    'stripe_account_id' => $user->stripe_account_id,
                    'error' => $e->getMessage(),
                ]);

                return view('stripe.connect', [
                    'account' => null,
                    'error' => 'Unable to retrieve your Stripe account status.',
                ]);
            }
            $status = $this->getAccountStatus($account);

            return view('stripe.connect', [
                'account' => $account,
                'status' => $status,
            ]);
        }

        return view('stripe.connect', [
            'account' => null,
            'error' => 'No account attached.',
        ]);

        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Generate a Stripe-hosted onboarding URL.
     */
    public function onboard(Request $request)
    {
        Log::info("In onboard");
        $user = Auth::user();

        if (!$user->stripe_account_id) {
            return redirect()
                ->route('stripe.connect.index')
                ->with('error', 'Create your Stripe account first.');
        }

        try {
            $accountLink = $this->stripe->client()
                ->v2
                ->core
                ->accountLinks
                ->create([
                    'account' => $user->stripe_account_id,

                    'use_case' => [
                        'type' => 'account_onboarding',

                        'account_onboarding' => [
                            /*
                                * Must exactly match the configurations
                                * applied to the account in create().
                                */
                            'configurations' => ['merchant'],

                            'refresh_url' => route(
                                'stripe.connect.refresh'
                            ),

                            'return_url' => route(
                                'stripe.connect.return',
                                [
                                    'accountId' =>
                                        $user->stripe_account_id,
                                ]
                            ),
                        ],
                    ],
                ]);

            /*
                * Account Links are temporary/single-use onboarding URLs.
                * Redirect the user immediately.
                */
            return redirect()->away($accountLink->url);

        } catch (Throwable $e) {

            Log::error('Stripe onboarding link creation failed.', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Unable to start Stripe onboarding: ' . $e->getMessage()
            );
        }
    }

    /**
     * Stripe redirects here after onboarding.
     *
     * IMPORTANT:
     * Returning here does not itself prove that onboarding is complete.
     * We retrieve the account from Stripe again.
     */
    public function return(Request $request)
    {
        return redirect()
            ->route('stripe.connect.index')
            ->with('success', 'Stripe onboarding returned successfully.');
    }


    /**
     * Stripe sends the user here when an Account Link expires or
     * otherwise needs to be regenerated.
     */
    public function refresh(Request $request)
    {
        return redirect()
            ->route('stripe.connect.index')
            ->with(
                'error',
                'Your Stripe onboarding link expired. Please start onboarding again.'
            );
    }

    private function getAccountStatus(StripeAccount $account): array
    {
        if (!$account) {
            return [
                'exists' => false,
                'payments_ready' => false,
                'onboarding_complete' => false,
                'requirements' => null,
            ];
        }

        /*
         * Determine whether card payments are active.
         */
        $paymentsReady =
            $account->configuration?->merchant
                ?->capabilities?->card_payments
                ?->status === 'active';

        /*
         * Determine whether Stripe still considers requirements
         * currently due or past due.
         */
        $requirementsStatus =
            $account->requirements
                ?->summary
                ?->minimum_deadline
                ?->status;

        $onboardingComplete =
            $requirementsStatus !== 'currently_due'
            && $requirementsStatus !== 'past_due';

        return [
            'exists' => true,
            'payments_ready' => $paymentsReady,
            'onboarding_complete' => $onboardingComplete,
            'requirements' => $requirementsStatus,
        ];
    }
}
