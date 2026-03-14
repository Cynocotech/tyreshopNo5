<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - {{ $siteName ?? 'NO5 Tyre & MOT' }}</title>
</head>
<body style="margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:#f1f5f9;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f1f5f9;">
<tr>
<td align="center" style="padding:32px 16px;">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);overflow:hidden;">
<!-- Header -->
<tr>
<td style="background:linear-gradient(135deg,#1B263B 0%,#2a3a52 100%);padding:32px 40px;text-align:center;">
@if(!empty($logoUrl))
<img src="{{ $logoUrl }}" alt="{{ $siteName ?? 'NO5' }}" style="max-height:56px;max-width:200px;display:inline-block;vertical-align:middle;" />
@else
<span style="color:#fede00;font-size:24px;font-weight:700;letter-spacing:0.5px;">{{ $siteName ?? 'NO5 Tyre & MOT' }}</span>
@endif
</td>
</tr>
<!-- Success badge -->
<tr>
<td style="padding:24px 40px 8px;text-align:center;">
<div style="display:inline-block;background:#dcfce7;color:#166534;padding:10px 20px;border-radius:999px;font-weight:600;font-size:14px;">✓ Booking Confirmed</div>
</td>
</tr>
<!-- Main content -->
<tr>
<td style="padding:16px 40px 32px;">
<h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#1B263B;">Hi {{ $customerName }},</h1>
<p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#475569;">Your MOT/service appointment has been confirmed. Here are your booking details:</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
<tr style="background:#f8fafc;"><td style="padding:12px 16px;font-weight:600;color:#64748B;font-size:13px;">Booking ID</td><td style="padding:12px 16px;font-weight:600;color:#1B263B;">{{ $bookingId }}</td></tr>
<tr><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Date & Time</td><td style="padding:12px 16px;color:#1B263B;">{{ $appointmentDate }} at {{ $appointmentTime }}</td></tr>
<tr style="background:#f8fafc;"><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Service</td><td style="padding:12px 16px;color:#1B263B;">{{ $serviceType }}</td></tr>
<tr><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Vehicle</td><td style="padding:12px 16px;color:#1B263B;">{{ $vehicleMake }} {{ $vehicleModel }} ({{ $vehicleRegistration }})</td></tr>
<tr style="background:#f8fafc;"><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Amount</td><td style="padding:12px 16px;color:#1B263B;font-weight:600;">£{{ $totalAmount }}</td></tr>
</table>

<p style="margin:24px 0 0;font-size:14px;line-height:1.6;color:#64748B;">We look forward to seeing you. If you need to reschedule, please contact us.</p>

@if(!empty($siteUrl))
<p style="margin:20px 0 0;text-align:center;">
<a href="{{ $siteUrl }}" style="display:inline-block;background:#1B263B;color:#fede00;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;">Visit our website</a>
</p>
@endif
</td>
</tr>
<!-- Footer -->
<tr>
<td style="background:#1B263B;padding:24px 40px;text-align:center;">
<p style="margin:0;font-size:13px;color:rgba(255,255,255,0.8);">{{ $siteName ?? 'NO5 Tyre & MOT' }} · {{ $phone ?? '' }}</p>
<p style="margin:8px 0 0;font-size:12px;color:rgba(255,255,255,0.6);">© {{ date('Y') }} {{ $siteName ?? 'NO5 Tyre & MOT' }}</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
