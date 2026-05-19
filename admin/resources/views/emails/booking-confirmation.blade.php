<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — {{ $siteName ?? 'Bourn Hill Tyre & MOT' }}</title>
</head>
<body style="margin:0;padding:0;font-family:'Instrument Sans','Segoe UI',Arial,sans-serif;background-color:#f4f4f4;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f4f4;">
<tr>
<td align="center" style="padding:32px 16px;">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.12);">

  <!-- Header -->
  <tr>
    <td style="background:#000000;padding:28px 40px;text-align:center;">
      <img src="https://no5tyreandmot.co.uk/Main-Logo.PNG" alt="{{ $siteName ?? 'Bourn Hill Tyre & MOT' }}" style="max-height:64px;max-width:220px;display:inline-block;" />
    </td>
  </tr>

  <!-- Yellow accent bar -->
  <tr>
    <td style="background:#fede00;padding:0;height:6px;font-size:0;line-height:0;">&nbsp;</td>
  </tr>

  <!-- Success badge -->
  <tr>
    <td style="background:#ffffff;padding:32px 40px 8px;text-align:center;">
      <div style="display:inline-block;background:#000000;color:#fede00;padding:10px 24px;border-radius:999px;font-weight:700;font-size:14px;letter-spacing:0.03em;">✓ Booking Confirmed</div>
    </td>
  </tr>

  <!-- Greeting -->
  <tr>
    <td style="background:#ffffff;padding:16px 40px 8px;">
      <h1 style="margin:0 0 8px;font-size:24px;font-weight:800;color:#111111;">Hi {{ $customerName }},</h1>
      <p style="margin:0 0 24px;font-size:16px;line-height:1.7;color:#444444;">Your booking is confirmed. Here's a summary of your appointment at <strong>Bourn Hill Tyre & MOT</strong>:</p>
    </td>
  </tr>

  <!-- Booking details table -->
  <tr>
    <td style="background:#ffffff;padding:0 40px 24px;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-radius:12px;overflow:hidden;border:2px solid #fede00;">
        <tr style="background:#fede00;">
          <td colspan="2" style="padding:10px 18px;font-size:12px;font-weight:800;color:#111111;letter-spacing:0.08em;text-transform:uppercase;">Booking Details</td>
        </tr>
        <tr style="background:#ffffff;">
          <td style="padding:12px 18px;font-weight:600;color:#888888;font-size:13px;width:40%;">Booking ID</td>
          <td style="padding:12px 18px;font-weight:700;color:#111111;font-size:13px;">{{ $bookingId }}</td>
        </tr>
        <tr style="background:#fafafa;">
          <td style="padding:12px 18px;font-weight:600;color:#888888;font-size:13px;">Date & Time</td>
          <td style="padding:12px 18px;font-weight:600;color:#111111;font-size:13px;">{{ $appointmentDate }} at {{ $appointmentTime }}</td>
        </tr>
        <tr style="background:#ffffff;">
          <td style="padding:12px 18px;font-weight:600;color:#888888;font-size:13px;">Service</td>
          <td style="padding:12px 18px;font-weight:600;color:#111111;font-size:13px;">{{ $serviceType }}</td>
        </tr>
        <tr style="background:#fafafa;">
          <td style="padding:12px 18px;font-weight:600;color:#888888;font-size:13px;">Vehicle</td>
          <td style="padding:12px 18px;font-weight:600;color:#111111;font-size:13px;">{{ $vehicleMake ?? '' }} {{ $vehicleModel ?? '' }} · {{ $vehicleRegistration }}</td>
        </tr>
        <tr style="background:#000000;">
          <td style="padding:14px 18px;font-weight:700;color:#fede00;font-size:14px;">Amount Due</td>
          <td style="padding:14px 18px;font-weight:800;color:#fede00;font-size:18px;">£{{ $totalAmount }}</td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Info box -->
  <tr>
    <td style="background:#ffffff;padding:0 40px 24px;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f9f9f9;border-left:4px solid #fede00;border-radius:0 8px 8px 0;padding:0;">
        <tr>
          <td style="padding:16px 20px;font-size:14px;line-height:1.7;color:#555555;">
            <strong style="color:#111111;">Pay on arrival.</strong> Please arrive 5 minutes before your appointment. If you need to reschedule or cancel, call us as soon as possible.<br><br>
            📍 <strong>6a Bourne Hill, Palmers Green, London N13 4LG</strong><br>
            📞 <strong>{{ $phone ?? '+447895859505' }}</strong><br>
            🕐 <strong>Mon–Sat: 8am–7pm · Sun: 9:30am–4pm</strong>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- CTA button -->
  @if(!empty($siteUrl))
  <tr>
    <td style="background:#ffffff;padding:0 40px 32px;text-align:center;">
      <a href="{{ $siteUrl }}" style="display:inline-block;background:#fede00;color:#111111;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:800;font-size:15px;">Visit Our Website →</a>
    </td>
  </tr>
  @endif

  <!-- Footer -->
  <tr>
    <td style="background:#111111;padding:4px 0;font-size:0;line-height:0;">&nbsp;</td>
  </tr>
  <tr>
    <td style="background:#000000;padding:24px 40px;text-align:center;">
      <p style="margin:0 0 6px;font-size:13px;color:rgba(255,255,255,0.7);">{{ $siteName ?? 'Bourn Hill Tyre & MOT' }} · 6a Bourne Hill, Palmers Green, London N13 4LG</p>
      <p style="margin:0 0 12px;font-size:12px;color:#ffffff;">© {{ date('Y') }} {{ $siteName ?? 'Bourn Hill Tyre & MOT' }}. All rights reserved.</p>
      <p style="margin:0;font-size:11px;color:#ffffff;">Powered by <a href="https://cybercina.co.uk" target="_blank" style="color:#ffffff;text-decoration:none;font-weight:600;">CYBERCINA</a></p>
    </td>
  </tr>

</table>
</td>
</tr>
</table>
</body>
</html>
