<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Store</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .products {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .product {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }

        button {
            width: 100%;
            background: #111;
            color: white;
            border: 0;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<h1>Store</h1>

@if(request('payment') === 'success')
    <p>
        Payment completed successfully.
    </p>
@endif

@if(session('error'))
    <p>
        {{ session('error') }}
    </p>
@endif

<div class="products">

    @forelse($products as $product)

        <div class="product">

            <h2>
                {{ $product->name }}
            </h2>

            @if($product->description)
                <p>
                    {{ $product->description }}
                </p>
            @endif

            @if($product->default_price)

                <p>
                    <strong>
                        {{ number_format(
                            $product->default_price->unit_amount / 100,
                            2
                        ) }}
                        {{ strtoupper(
                            $product->default_price->currency
                        ) }}
                    </strong>
                </p>

                <form
                    method="POST"
                    action="{{ route(
                        'stripe.store.checkout',
                        ['accountId' => $accountId]
                    ) }}"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="product_id"
                        value="{{ $product->id }}"
                    >

                    <button type="submit">
                        Buy now
                    </button>
                </form>

            @endif

        </div>

    @empty

        <p>
            This store currently has no products.
        </p>

    @endforelse

</div>

</body>
</html>