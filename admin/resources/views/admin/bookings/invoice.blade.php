<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice — {{ $booking->booking_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; color: #1e293b; background: #fff; }
        .invoice-page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .business h1 { font-size: 1.35rem; font-weight: 700; color: #000; margin-bottom: 0.35rem; }
        .business p { font-size: 0.9rem; color: #333; line-height: 1.5; }
        .invoice-right {
            text-align: right;
        }
        .invoice-right .invoice-title { font-size: 1.5rem; font-weight: 700; color: #2563eb; margin-bottom: 0.75rem; }
        .invoice-right .invoice-meta-table { font-size: 0.9rem; }
        .invoice-right .invoice-meta-table td { padding: 2px 0 2px 12px; text-align: right; }
        .invoice-right .invoice-meta-table td:first-child { color: #64748b; padding-right: 8px; font-weight: 500; }
        .bill-to-section { margin: 1.5rem 0 1.5rem 0; }
        .bill-to-section .bill-to-label {
            background: #bfdbfe;
            color: #1e40af;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 8px 12px;
            margin-bottom: 0;
        }
        .bill-to-section .bill-to-content {
            border: 1px solid #e2e8f0;
            border-top: none;
            padding: 12px;
            line-height: 1.6;
            font-size: 0.9rem;
        }
        .items-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .items-table th {
            background: #bfdbfe;
            color: #1e40af;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #93c5fd;
        }
        .items-table th:last-child { text-align: right; }
        .items-table td {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        .items-table td:last-child { text-align: right; font-weight: 600; }
        .items-table tbody tr:nth-child(even) { background: #f8fafc; }
        .total-row td {
            font-weight: 700;
            font-size: 1rem;
            background: #f1f5f9 !important;
            padding: 12px;
            border-top: 2px solid #94a3b8;
        }
        .thank-you { text-align: center; margin: 1rem 0 0.5rem; font-size: 1rem; color: #475569; }
        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.5;
        }
        .footer strong { color: #334155; }
        .print-btn { position: fixed; top: 16px; right: 16px; padding: 10px 20px; background: #1B263B; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        @page { size: A4; margin: 15mm; }
        @media print {
            body { background: #fff; }
            .print-btn { display: none; }
            .invoice-page { padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <button type="button" class="print-btn" onclick="window.print()">Print</button>

    <div class="invoice-page">
        <div class="invoice-header">
            <div class="business">
                <h1>{{ $business['name'] }}</h1>
                @if(!empty($business['address']))<p>{{ $business['address'] }}</p>@endif
                @if(!empty($business['phone']))<p>Phone: {{ $business['phone'] }}</p>@endif
                @if(!empty($business['email']))<p>{{ $business['email'] }}</p>@endif
            </div>
            <div class="invoice-right">
                <div class="invoice-title">INVOICE</div>
                <table class="invoice-meta-table" style="margin-left: auto;">
                    <tr>
                        <td>INVOICE #</td>
                        <td>{{ $booking->booking_id }}</td>
                    </tr>
                    <tr>
                        <td>DATE</td>
                        <td>{{ $booking->appointment_date->format('j/n/Y') }}</td>
                    </tr>
                    <tr>
                        <td>APPOINTMENT</td>
                        <td>{{ $booking->appointment_date->format('D, j M Y') }} at {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}</td>
                    </tr>
                    @if($booking->vehicle_registration)
                    <tr>
                        <td>VRN</td>
                        <td>{{ $booking->vehicle_registration }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="bill-to-section">
            <div class="bill-to-label">BILL TO</div>
            <div class="bill-to-content">
                {{ $booking->customer_name ?? '—' }}<br>
                @if($booking->customer_phone)Phone: {{ $booking->customer_phone }}<br>@endif
                @if($booking->customer_email){{ $booking->customer_email }}<br>@endif
                @if($booking->vehicle_registration)<br><strong>VRN:</strong> {{ $booking->vehicle_registration }}@endif
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>DESCRIPTION</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $vehicle = trim($booking->vehicle_registration . ' ' . ($booking->vehicle_make ?? '') . ' ' . ($booking->vehicle_model ?? ''));
                    $description = ($booking->service_type ?? 'Service');
                    if ($vehicle) {
                        $description .= ' – Vehicle: ' . $vehicle;
                    }
                @endphp
                <tr>
                    <td>{{ $description }}</td>
                    <td>£{{ $booking->total_amount ? number_format((float) $booking->total_amount, 2) : '0.00' }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="1" style="text-align: right;">TOTAL</td>
                    <td>£{{ $booking->total_amount ? number_format((float) $booking->total_amount, 2) : '0.00' }}</td>
                </tr>
            </tbody>
        </table>

        <p class="thank-you">Thank you for your business!</p>

        <div class="footer">
            <p>If you have any questions about this invoice, please contact</p>
            <p><strong>{{ $business['name'] }} — {{ $business['phone'] ?? '' }} — {{ $business['email'] ?? '' }}</strong></p>
        </div>
    </div>

    <script>
        if (window.location.search.includes('print=1')) window.print();
    </script>
</body>
</html>
