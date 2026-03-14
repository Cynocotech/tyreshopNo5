<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - {{ $siteName ?? 'NO5 Tyre & MOT' }}</title>
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
<!-- Thank you badge -->
<tr>
<td style="padding:24px 40px 8px;text-align:center;">
<div style="display:inline-block;background:#dcfce7;color:#166534;padding:10px 20px;border-radius:999px;font-weight:600;font-size:14px;">✓ Thank you for visiting</div>
</td>
</tr>
<!-- Main content -->
<tr>
<td style="padding:16px 40px 32px;">
<h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#1B263B;">Hi {{ $customerName }},</h1>
<p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#475569;">Thank you for choosing us for your {{ $serviceType ?? 'service' }} today. We hope you're satisfied with the work we did.</p>
<p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#475569;">Your feedback means a lot to us and helps other customers find trusted service. If you have a moment, we'd really appreciate a review on Google.</p>
@if(!empty($googleReviewUrl))
<p style="margin:0 0 0;text-align:center;">
<a href="{{ $googleReviewUrl }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:#4285F4;color:#fff;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;">
<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
Rate us on Google
</a>
</p>
@endif
<p style="margin:24px 0 0;font-size:14px;line-height:1.6;color:#64748B;">We look forward to serving you again. Drive safe!</p>
@if(!empty($siteUrl))
<p style="margin:20px 0 0;text-align:center;">
<a href="{{ $siteUrl }}" style="display:inline-block;background:#1B263B;color:#fede00;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;">Visit our website</a>
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
