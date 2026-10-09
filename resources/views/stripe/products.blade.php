<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        input,
        textarea,
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin: 5px 0 15px;
        }

        button {
            background: #111;
            color: white;
            border: 0;
            padding: 12px 20px;
            border-radius: 5px;
        }

        .product {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
    </style>
</head>

<body>

<h1>My Products</h1>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

@if(session('error'))
    <p>{{ session('error') }}</p>
@endif


<div class="card">

    <h2>Create Product</h2>

    <form method="POST" action="{{ route('stripe.products.store') }}">
        @csrf

        <label>Name</label>
        <input name="name" required>

        <label>Description</label>
        <textarea name="description"></textarea>

        <label>Price</label>
        <input
            type="number"
            name="price"
            step="0.01"
            min="0.50"
            required
        >

        <label>Currency</label>

        <select name="currency">
            <option value="usd">USD</option>
            <option value="cad">CAD</option>
            <option value="eur">EUR</option>
            <option value="gbp">GBP</option>
        </select>

        <button type="submit">
            Create product
        </button>
    </form>

</div>


<div class="card">

    <h2>Existing Products</h2>

    @forelse($products as $product)

        <div class="product">

            <div>
                <strong>
                    {{ $product->name }}
                </strong>

                @if($product->description)
                    <div>
                        {{ $product->description }}
                    </div>
                @endif
            </div>

            @if($product->default_price)
                <strong>
                    {{ number_format(
                        $product->default_price->unit_amount / 100,
                        2
                    ) }}
                    {{ strtoupper($product->default_price->currency) }}
                </strong>
            @endif

        </div>

    @empty

        <p>No products yet.</p>

    @endforelse

</div>

</body>
</html>