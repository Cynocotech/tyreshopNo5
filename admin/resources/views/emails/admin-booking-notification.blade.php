<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking - {{ $bookingId }}</title>
</head>
<body style="margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:#f1f5f9;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f1f5f9;">
<tr>
<td align="center" style="padding:32px 16px;">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);overflow:hidden;">
<!-- Header -->
<tr>
<td style="background:linear-gradient(135deg,#1B263B 0%,#2a3a52 100%);padding:24px 40px;">
<span style="color:#fede00;font-size:20px;font-weight:700;">{{ $siteName ?? 'NO5' }} Admin</span>
<span style="color:rgba(255,255,255,0.9);font-size:14px;margin-left:12px;">— New Booking</span>
</td>
</tr>
<!-- Content -->
<tr>
<td style="padding:32px 40px;">
<h2 style="margin:0 0 24px;font-size:18px;font-weight:700;color:#1B263B;">New MOT/Service Booking</h2>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
<tr style="background:#f8fafc;"><td style="padding:12px 16px;font-weight:600;color:#64748B;font-size:13px;width:140px;">Booking ID</td><td style="padding:12px 16px;font-weight:600;color:#1B263B;">{{ $bookingId }}</td></tr>
<tr><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Customer</td><td style="padding:12px 16px;color:#1B263B;">{{ $customerName }}</td></tr>
<tr style="background:#f8fafc;"><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Email</td><td style="padding:12px 16px;color:#1B263B;"><a href="mailto:{{ $customerEmail }}" style="color:#1B263B;text-decoration:none;">{{ $customerEmail }}</a></td></tr>
<tr><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Phone</td><td style="padding:12px 16px;color:#1B263B;">{{ $customerPhone }}</td></tr>
<tr style="background:#f8fafc;"><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Vehicle</td><td style="padding:12px 16px;color:#1B263B;">{{ $vehicleMake }} {{ $vehicleModel }} ({{ $vehicleRegistration }})</td></tr>
<tr><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Date & Time</td><td style="padding:12px 16px;color:#1B263B;">{{ $appointmentDate }} at {{ $appointmentTime }}</td></tr>
<tr style="background:#f8fafc;"><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Service</td><td style="padding:12px 16px;color:#1B263B;">{{ $serviceType }}</td></tr>
<tr><td style="padding:12px 16px;font-weight:500;color:#64748B;font-size:13px;">Amount</td><td style="padding:12px 16px;color:#1B263B;font-weight:600;">£{{ $totalAmount }}</td></tr>
</table>
</td>
</tr>
<!-- Footer -->
<tr>
<td style="background:#1B263B;padding:16px 40px;text-align:center;">
<p style="margin:0;font-size:12px;color:rgba(255,255,255,0.6);">Admin notification · {{ $siteName ?? 'NO5 Tyre & MOT' }}</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
