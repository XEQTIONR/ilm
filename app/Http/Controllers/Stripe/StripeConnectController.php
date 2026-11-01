<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Api\Stripe\StripeConnectController as ApiController;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeConnectController extends ApiController
{
    public function __construct(
        private StripeService $stripe
    ) {
        parent::__construct($stripe);
    }

    /**
     * Show creation form.
     */
        /**
     * Create the Stripe V2 connected account.
     */
    public function create(Request $request)
    {
        $res = parent::create($request);

        Log::info("In create", $res);

        switch ($res['status']) {
            case 'success':
                return redirect()->route('stripe.connect.index')->with('success', $res['message']);
            case 'error':
                return back()->with('error', $res['errors']);
            default:
                return redirect()->route('stripe.connect.index')->with('error', 'Something went wrong.');
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $res = parent::index();
        
        Log::info("In index", $res);
        
        switch ($res['status']) {
            case 'success':
                return view('stripe.connect', [
                    'account' => $res['account'],
                    'status' => $res['account_status'],
                ]);
            case 'error':
                return view('stripe.connect', [
                    'account' => null,
                    'error' => $res['errors'],
                ]);
            default:
                return redirect()->route('stripe.connect.index')->with('error', 'Something went wrong.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Generate a Stripe-hosted onboarding URL and send the browser there.
     */
    public function onboard(Request $request)
    {
        $res = parent::onboard($request);

        if (($res['status'] ?? null) === 'success') {
            return redirect()->away($res['url']);
        }

        return redirect()
            ->route('stripe.connect.index')
            ->with('error', $res['errors'] ?? $res['message'] ?? 'Unable to start Stripe onboarding.');
    }

    /**
     * Stripe redirects here after onboarding.
     *
     * Returning here does not itself prove that onboarding is complete.
     * The API controller retrieves the account from Stripe again.
     */
    public function return(Request $request)
    {
        $res = parent::return($request);

        $complete = ($res['status'] ?? null) === 'success'
            && ($res['onboarding_complete'] ?? false)
            && ($res['payments_ready'] ?? false);

        return redirect()
            ->route('stripe.connect.index')
            ->with(
                $complete ? 'success' : 'error',
                $res['message'] ?? 'Unable to confirm Stripe onboarding.'
            );
    }

    public function delete()
    {
        $res = parent::delete();

        switch ($res['status']) {
            case 'success':
                return redirect()->route('stripe.connect.index')->with('success', $res['message']);
            case 'error':
                return back()->with('error', $res['errors']);
            default:
                return redirect()->route('stripe.connect.index')->with('error', 'Something went wrong.');
        }
    }


    /**
     * Stripe sends the user here when an Account Link expires or
     * otherwise needs to be regenerated. Mint a new link immediately.
     */
    public function refresh(Request $request)
    {
        return $this->onboard($request);
    }

    /**
     * Public bounce for the iOS app. Stripe can only redirect to http(s),
     * so this page sends the browser on to the iqra:// callback.
     */
    public function mobileReturn(Request $request)
    {
        return $this->redirectToMobileApp($request, '/connect/return');
    }

    public function mobileRefresh(Request $request)
    {
        return $this->redirectToMobileApp($request, '/connect/refresh');
    }

    private function redirectToMobileApp(Request $request, string $path)
    {
        $target = $request->string('target')->toString();
        $parts = parse_url($target);
        $targetPath = rtrim(is_array($parts) ? ($parts['path'] ?? '') : '', '/');

        $allowed = is_array($parts)
            && ($parts['scheme'] ?? null) === 'iqra'
            && ($parts['host'] ?? null) === 'stripe'
            && $targetPath === $path;

        abort_unless($allowed, 400, 'Invalid return URL.');

        return redirect()->away($target);
    }
}
