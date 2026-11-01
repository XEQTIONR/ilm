<?php

namespace App\Http\Controllers\Api\Stripe;

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
        @TODO:
            */
        if ($user->stripe_account_id) {
            return [
                'status' => 'error',
                'message' => 'Stripe could not create your account: Your Stripe account already exists.',
                'errors' => 'Your Stripe account already exists.',
            ];
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
            
            return [
                'status' => 'success',
                'message' => 'Your Stripe account was created.',
                'account_id' => $account->id,
            ];

        } catch (Throwable $e) {
            
            return [
                'status' => 'error',
                'message' => 'Stripe could not create your account: ' . $e->getMessage(),
                'errors' => $e->getMessage(),
            ];
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
                return [
                    'status' => 'error',
                    'message' => 'Unable to retrieve your Stripe account status.',
                    'errors' => $e->getMessage(),
                ];
            }
        }

        return [
            'status' => 'success',
            'message' => 'Stripe account retrieved successfully.',
            'account' => $account,
            'account_status' => $this->getAccountStatus($account),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->create($request);
    }

    /**
     * Create a Stripe-hosted onboarding link for the connected account.
     *
     * The configurations sent here must be exactly the configurations
     * already applied to the account. Return and refresh URLs from the
     * request are only honored on the API, so the web flow always lands
     * back on the session-authenticated pages.
     */
    public function onboard(Request $request)
    {
        $user = Auth::user();

        if (! $user->stripe_account_id) {
            return [
                'status' => 'error',
                'message' => 'Create your Stripe account first.',
                'errors' => 'Create your Stripe account first.',
            ];
        }

        try {
            $account = $this->stripe->client()
                ->v2
                ->core
                ->accounts
                ->retrieve($user->stripe_account_id);

            $accountLink = $this->stripe->client()
                ->v2
                ->core
                ->accountLinks
                ->create([
                    'account' => $user->stripe_account_id,
                    'use_case' => [
                        'type' => 'account_onboarding',
                        'account_onboarding' => [
                            'configurations' => $this->onboardingConfigurations($account),
                            'refresh_url' => $this->onboardingRefreshUrl($request),
                            'return_url' => $this->onboardingReturnUrl($request, $user->stripe_account_id),
                        ],
                    ],
                ]);

            return [
                'status' => 'success',
                'message' => 'Stripe onboarding link created.',
                'url' => $accountLink->url,
            ];
        } catch (Throwable $e) {
            Log::error('Stripe onboarding link creation failed.', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Unable to start Stripe onboarding: '.$e->getMessage(),
                'errors' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm onboarding by reading the account from Stripe again.
     *
     * Landing on the return URL does not itself mean onboarding finished.
     */
    public function return(Request $request)
    {
        $res = $this->index();

        if (($res['status'] ?? null) !== 'success') {
            return [
                'status' => 'error',
                'message' => $res['message'] ?? 'Unable to confirm Stripe onboarding.',
                'errors' => $res['errors'] ?? 'Unable to confirm Stripe onboarding.',
                'onboarding_complete' => false,
                'payments_ready' => false,
            ];
        }

        $status = $res['account_status'];
        $complete = (bool) ($status['onboarding_complete'] ?? false);
        $ready = (bool) ($status['payments_ready'] ?? false);

        return [
            'status' => 'success',
            'message' => $complete && $ready
                ? 'Stripe onboarding is complete. Your account can process payments.'
                : 'Stripe onboarding returned, but your account still has outstanding requirements.',
            'onboarding_complete' => $complete,
            'payments_ready' => $ready,
            'account' => $res['account'],
            'account_status' => $status,
        ];
    }

    /**
     * Stripe sends the browser here when an Account Link expires.
     * The refresh URL has to mint a new link.
     */
    public function refresh(Request $request)
    {
        return $this->onboard($request);
    }

    public function delete()
    {
        $user = Auth::user();

        if (!$user->stripe_account_id) {
            return [
                'status' => 'error',
                'message' => 'Create your Stripe account first.',
            ];
        }

        try {
            /*
                * Accounts v2 has no delete endpoint. Close removes access
                * to the account. Every applied configuration must be named
                * or Stripe rejects the request.
                */
            $this->stripe->client()
                ->v2
                ->core
                ->accounts
                ->close($user->stripe_account_id, [
                    'applied_configurations' => ['merchant'],
                ]);

            $user->stripe_account_id = null;
            $user->save();

            return [
                'status' => 'success',
                'message' => 'Stripe account deleted successfully.',
            ];

        } catch (Throwable $e) {
            Log::error('Stripe connected account deletion failed.', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'errors' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Stripe could not delete your account: ' . $e->getMessage(),
                'errors' => $e->getMessage(),
            ];
        }
    }

    private function onboardingConfigurations(StripeAccount $account): array
    {
        $applied = $account->applied_configurations ?? null;

        if (is_object($applied) && method_exists($applied, 'toArray')) {
            $applied = $applied->toArray();
        }

        $configurations = array_values(array_filter(
            (array) $applied,
            fn ($configuration) => is_string($configuration) && $configuration !== ''
        ));

        return $configurations !== [] ? $configurations : ['merchant'];
    }

    private function onboardingReturnUrl(Request $request, string $accountId): string
    {
        $fallback = route('stripe.connect.return', ['accountId' => $accountId]);

        if (! $request->is('api/*')) {
            return $fallback;
        }

        $returnUrl = $request->string('return_url')->toString();

        return $this->stripeRedirectUrl($returnUrl, 'stripe.connect.mobile.return', $fallback);
    }

    private function onboardingRefreshUrl(Request $request): string
    {
        $fallback = route('stripe.connect.refresh');

        if (! $request->is('api/*')) {
            return $fallback;
        }

        $refreshUrl = $request->string('refresh_url')->toString();

        return $this->stripeRedirectUrl($refreshUrl, 'stripe.connect.mobile.refresh', $fallback);
    }

    /**
     * Stripe rejects custom schemes such as iqra:// as "not a valid URL".
     * Those addresses are wrapped in an http bounce that redirects back into the app.
     * Test mode accepts http, including localhost.
     */
    private function stripeRedirectUrl(string $url, string $bounceRoute, string $fallback): string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return $fallback;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (in_array($scheme, ['http', 'https'], true)) {
            return $url;
        }

        return route($bounceRoute, ['target' => $url]);
    }

    private function getAccountStatus(?StripeAccount $account): array
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