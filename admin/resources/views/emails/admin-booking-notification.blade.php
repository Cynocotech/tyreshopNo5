<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking — {{ $bookingId }}</title>
</head>
<body style="margin:0;padding:0;font-family:'Instrument Sans','Segoe UI',Arial,sans-serif;background-color:#f4f4f4;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f4f4;">
<tr>
<td align="center" style="padding:32px 16px;">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.12);">

  <!-- Header -->
  <tr>
    <td style="background:#000000;padding:20px 32px;">
      <span style="color:#fede00;font-size:18px;font-weight:800;">{{ $siteName ?? 'Bourn Hill Tyre & MOT' }}</span>
      <span style="color:rgba(255,255,255,0.6);font-size:14px;margin-left:10px;">· New Booking Alert</span>
    </td>
  </tr>
  <tr>
    <td style="background:#fede00;padding:0;height:4px;font-size:0;line-height:0;">&nbsp;</td>
  </tr>

  <!-- Content -->
  <tr>
    <td style="background:#ffffff;padding:28px 32px 8px;">
      <h2 style="margin:0 0 6px;font-size:20px;font-weight:800;color:#111111;">🛞 New Booking Received</h2>
      <p style="margin:0 0 24px;font-size:14px;color:#666666;">A new booking has been submitted. Details below:</p>
    </td>
  </tr>

  <!-- Details -->
  <tr>
    <td style="background:#ffffff;padding:0 32px 28px;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-radius:12px;overflow:hidden;border:2px solid #fede00;">
        <tr style="background:#fede00;">
          <td colspan="2" style="padding:10px 18px;font-size:12px;font-weight:800;color:#111111;letter-spacing:0.08em;text-transform:uppercase;">Booking Summary</td>
        </tr>
        <tr style="background:#ffffff;">
          <td style="padding:11px 18px;font-weight:600;color:#888888;font-size:13px;width:38%;">Booking ID</td>
          <td style="padding:11px 18px;font-weight:700;color:#111111;font-size:13px;">{{ $bookingId }}</td>
        </tr>
        <tr style="background:#fafafa;">
          <td style="padding:11px 18px;font-weight:600;color:#888888;font-size:13px;">Customer</td>
          <td style="padding:11px 18px;font-weight:600;color:#111111;font-size:13px;">{{ $customerName }}</td>
        </tr>
        <tr style="background:#ffffff;">
          <td style="padding:11px 18px;font-weight:600;color:#888888;font-size:13px;">Email</td>
          <td style="padding:11px 18px;font-size:13px;"><a href="mailto:{{ $customerEmail }}" style="color:#111111;font-weight:600;">{{ $customerEmail }}</a></td>
        </tr>
        <tr style="background:#fafafa;">
          <td style="padding:11px 18px;font-weight:600;color:#888888;font-size:13px;">Phone</td>
          <td style="padding:11px 18px;font-weight:600;color:#111111;font-size:13px;">{{ $customerPhone }}</td>
        </tr>
        <tr style="background:#ffffff;">
          <td style="padding:11px 18px;font-weight:600;color:#888888;font-size:13px;">Vehicle</td>
          <td style="padding:11px 18px;font-weight:600;color:#111111;font-size:13px;">{{ $vehicleMake ?? '' }} {{ $vehicleModel ?? '' }} · {{ $vehicleRegistration }}</td>
        </tr>
        <tr style="background:#fafafa;">
          <td style="padding:11px 18px;font-weight:600;color:#888888;font-size:13px;">Date & Time</td>
          <td style="padding:11px 18px;font-weight:600;color:#111111;font-size:13px;">{{ $appointmentDate }} at {{ $appointmentTime }}</td>
        </tr>
        <tr style="background:#ffffff;">
          <td style="padding:11px 18px;font-weight:600;color:#888888;font-size:13px;">Service</td>
          <td style="padding:11px 18px;font-weight:600;color:#111111;font-size:13px;">{{ $serviceType }}</td>
        </tr>
        <tr style="background:#000000;">
          <td style="padding:14px 18px;font-weight:700;color:#fede00;font-size:14px;">Amount</td>
          <td style="padding:14px 18px;font-weight:800;color:#fede00;font-size:18px;">£{{ $totalAmount }}</td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#000000;padding:20px 32px;text-align:center;">
      <p style="margin:0 0 8px;font-size:12px;color:#ffffff;">Admin notification · {{ $siteName ?? 'Bourn Hill Tyre & MOT' }} · {{ date('Y') }}</p>
      <p style="margin:0;font-size:11px;color:#ffffff;">Powered by <a href="https://cybercina.co.uk" target="_blank" style="color:#ffffff;text-decoration:none;font-weight:600;">CYBERCINA</a></p>
    </td>
  </tr>

</table>
</td>
</tr>
</table>
</body>
</html>
