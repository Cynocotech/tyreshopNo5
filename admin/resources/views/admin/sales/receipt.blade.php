<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt — {{ $sale->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; color: #1e293b; background: #fff; }
        .receipt-page {
            width: 210mm;
            max-width: 100%;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
        }
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 1rem;
        }
        .business h1 { font-size: 1.35rem; font-weight: 700; color: #000; margin-bottom: 0.35rem; }
        .business p { font-size: 0.9rem; color: #333; line-height: 1.5; }
        .receipt-right { text-align: right; }
        .receipt-right .receipt-title { font-size: 1.5rem; font-weight: 700; color: #15803d; margin-bottom: 0.5rem; }
        .receipt-right .receipt-meta { font-size: 0.9rem; }
        .receipt-right .receipt-meta td { padding: 2px 0 2px 12px; text-align: right; }
        .receipt-right .receipt-meta td:first-child { color: #64748b; padding-right: 8px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .items-table th {
            background: #dcfce7;
            color: #166534;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #bbf7d0;
        }
        .items-table th:last-child { text-align: right; }
        .items-table td {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        .items-table td:last-child { text-align: right; font-weight: 600; }
        .total-row td {
            font-weight: 700;
            font-size: 1.1rem;
            background: #f0fdf4 !important;
            padding: 12px;
            border-top: 2px solid #22c55e;
        }
        .payment-info {
            margin-top: 1rem;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .payment-info p { font-size: 0.9rem; margin: 4px 0; }
        .thank-you { text-align: center; margin: 1.5rem 0; font-size: 1rem; color: #475569; }
        .print-btn { position: fixed; top: 16px; right: 16px; padding: 10px 20px; background: #15803d; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .print-btn { display: none; }
            .receipt-page { padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <button type="button" class="print-btn" onclick="window.print()">Print Receipt</button>

    <div class="receipt-page">
        <div class="receipt-header">
            <div class="business">
                <h1>{{ $business['name'] }}</h1>
                @if(!empty($business['address']))<p>{{ $business['address'] }}</p>@endif
                @if(!empty($business['phone']))<p>{{ $business['phone'] }}</p>@endif
            </div>
            <div class="receipt-right">
                <div class="receipt-title">RECEIPT</div>
                <table class="receipt-meta" style="margin-left: auto;">
                    <tr><td>Receipt #</td><td>{{ $sale->reference }}</td></tr>
                    <tr><td>Date</td><td>{{ $sale->completed_at?->format('j M Y H:i') ?? now()->format('j M Y H:i') }}</td></tr>
                    <tr><td>Payment</td><td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'N/A')) }}</td></tr>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Product' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>£{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>£{{ number_format((float) $item->total, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">TOTAL</td>
                    <td>£{{ number_format((float) $sale->total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @if($sale->booking_id)
        <div class="payment-info" style="margin-bottom: 1rem;">
            <p><strong>Booking:</strong> {{ $sale->booking_id }}</p>
        </div>
        @endif

        @if($sale->customer_name || $sale->customer_email || $sale->customer_phone || $sale->customer_vrn || $sale->customer_address)
        <div class="payment-info" style="margin-bottom: 1rem;">
            <strong style="display: block; margin-bottom: 6px;">Customer</strong>
            @if($sale->customer_name)<p>{{ $sale->customer_name }}</p>@endif
            @if($sale->customer_email)<p>{{ $sale->customer_email }}</p>@endif
            @if($sale->customer_phone)<p>{{ $sale->customer_phone }}</p>@endif
            @if($sale->customer_vrn)<p><strong>VRN:</strong> {{ $sale->customer_vrn }}</p>@endif
            @if($sale->customer_address)<p style="white-space: pre-line;">{{ $sale->customer_address }}</p>@endif
        </div>
        @endif

        @if($sale->payment_method === 'cash' && $sale->amount_tendered)
        <div class="payment-info">
            <p><strong>Amount tendered:</strong> £{{ number_format((float) $sale->amount_tendered, 2) }}</p>
            <p><strong>Change due:</strong> £{{ number_format(max(0, (float) $sale->amount_tendered - (float) $sale->total), 2) }}</p>
        </div>
        @endif

        <p class="thank-you">Thank you for your business!</p>
    </div>

    @if(request()->query('print') === '1')
    <script>window.print();</script>
    @endif
</body>
</html>
