<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stripe Connect</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 760px;
            margin: 50px auto;
            padding: 20px;
            color: #222;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 20px;
        }

        .status {
            padding: 10px 14px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .success {
            background: #e8f7ed;
            color: #176b36;
        }

        .warning {
            background: #fff6dd;
            color: #765900;
        }

        .error {
            background: #fdecec;
            color: #8a1f1f;
        }

        button {
            background: #111;
            color: white;
            border: 0;
            border-radius: 5px;
            padding: 12px 18px;
            cursor: pointer;
        }

        a.button {
            display: inline-block;
            background: #111;
            color: white;
            padding: 12px 18px;
            border-radius: 5px;
            text-decoration: none;
        }

        dl {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 10px;
        }

        dt {
            font-weight: bold;
        }
    </style>
</head>

<body>

<h1>Stripe Connect</h1>

@if(session('success'))
    <div class="status success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="status error">
        {{ session('error') }}
    </div>
@endif

@if(isset($error))
    <div class="status error">
        {{ $error }}
    </div>
@endif


@if(!$account)

    <div class="card">
        <h2>Start selling</h2>

        <p>
            Connect your Stripe account so customers can purchase
            products from you.
        </p>

        <form method="POST" action="{{ route('stripe.connect.create') }}">
            @csrf

            <button type="submit">
                Create Stripe account
            </button>
        </form>
    </div>

@else

    <div class="card">

        <h2>Stripe account</h2>

        <dl>
            <dt>Account ID</dt>
            <dd>{{ $account->id }}</dd>

            <dt>Onboarding</dt>
            <dd>
                @if($status['onboarding_complete'])
                    <span class="status success">
                        Complete
                    </span>
                @else
                    <span class="status warning">
                        Incomplete
                    </span>
                @endif
            </dd>

            <dt>Card payments</dt>
            <dd>
                @if($status['payments_ready'])
                    <span class="status success">
                        Ready
                    </span>
                @else
                    <span class="status warning">
                        Not ready
                    </span>
                @endif
            </dd>

            <dt>Requirements</dt>
            <dd>
                {{ $status['requirements'] ?? 'None reported' }}
            </dd>
        </dl>

        @if(!$status['onboarding_complete'] || !$status['payments_ready'])

            <p>
                Stripe still requires additional information before
                payments can be processed.
            </p>

            <a
                class="button"
                href="{{ route('stripe.connect.onboard') }}"
            >
                Onboard to collect payments
            </a>

        @else

            <div class="status success">
                Your account is ready to process payments.
            </div>

        @endif

        <form method="POST" action="{{ route('stripe.connect.delete') }}" style="margin-top: 20px;">
            @csrf
            @method('DELETE')

            <button type="submit">
                Delete Stripe account
            </button>
        </form>

    </div>

@endif

</body>
</html>