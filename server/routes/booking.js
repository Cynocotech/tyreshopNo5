/**
 * MOT booking: create booking, Stripe Checkout, Telegram notify, send email, SMS confirmation
 */
const express = require('express');
const router = express.Router();
const Stripe = require('stripe');
const TelegramBot = require('node-telegram-bot-api');
const nodemailer = require('nodemailer');
const fs = require('fs');
const path = require('path');
const https = require('https');

const stripe = process.env.STRIPE_SECRET_KEY ? new Stripe(process.env.STRIPE_SECRET_KEY) : null;
const stripePublishableKey = process.env.STRIPE_PUBLISHABLE_KEY || '';
const telegramToken = process.env.TELEGRAM_BOT_TOKEN;
const telegramChatId = process.env.TELEGRAM_CHAT_ID;

const MOT_PRICE_GBP = 1900; // £19.00 in pence
const MOT_SERVICE_PRICE_GBP = 1900; // £19 MOT + Service combo

const bot = telegramToken ? new TelegramBot(telegramToken) : null;

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST || 'smtp.ethereal.email',
  port: parseInt(process.env.SMTP_PORT || '587', 10),
  secure: process.env.SMTP_SECURE === 'true',
  auth: process.env.SMTP_USER ? {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS
  } : undefined
});

function loadEmailTemplate(data) {
  const templatePath = path.join(__dirname, '../views/booking-email.html');
  let html = fs.existsSync(templatePath)
    ? fs.readFileSync(templatePath, 'utf8')
    : getDefaultEmailHtml(data);
  Object.keys(data).forEach(k => {
    html = html.replace(new RegExp(`{{${k}}}`, 'g'), String(data[k] || ''));
  });
  return html;
}

// Timetable: Mon–Sat 8am–6pm, 30-min slots
const ALL_SLOTS = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00'];
const SLOTS_FILE = path.join(__dirname, '../data/booking-slots.json');

function readBookedSlots() {
  try {
    const dir = path.dirname(SLOTS_FILE);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    if (fs.existsSync(SLOTS_FILE)) {
      const raw = fs.readFileSync(SLOTS_FILE, 'utf8');
      const data = JSON.parse(raw);
      return data.slots || {};
    }
  } catch (e) { /* ignore */ }
  return {};
}

function saveBookedSlot(date, time) {
  const slots = readBookedSlots();
  if (!slots[date]) slots[date] = [];
  if (!slots[date].includes(time)) slots[date].push(time);
  slots[date].sort();
  const dir = path.dirname(SLOTS_FILE);
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(SLOTS_FILE, JSON.stringify({ slots }, null, 2), 'utf8');
}

function getDefaultEmailHtml(d) {
  return `
    <div style="font-family:sans-serif;max-width:600px;margin:0 auto;">
      <h1 style="color:#1B263B;">MOT Booking Confirmation</h1>
      <p>Dear ${d.customerName || 'Customer'},</p>
      <p>Your MOT booking has been confirmed.</p>
      <table style="border-collapse:collapse;width:100%;">
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Booking ID</strong></td><td style="padding:8px;border:1px solid #eee;">${d.bookingId || '-'}</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Vehicle</strong></td><td style="padding:8px;border:1px solid #eee;">${d.vehicleMake} ${d.vehicleModel} (${d.vehicleRegistration})</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Date & Time</strong></td><td style="padding:8px;border:1px solid #eee;">${d.appointmentDate} at ${d.appointmentTime}</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Service</strong></td><td style="padding:8px;border:1px solid #eee;">${d.serviceType || 'MOT Test'}</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Total</strong></td><td style="padding:8px;border:1px solid #eee;">£${d.totalAmount || '19.00'}</td></tr>
      </table>
      <p style="margin-top:24px;">Address: 6A Bourne Hill, Southgate, London N13 4LG</p>
      <p>Tel: 07895 859505</p>
      <hr style="border:none;border-top:1px solid #eee;margin:24px 0;">
      <p style="color:#64748B;font-size:12px;">N05 Tyre & MOT Service · Palmers Green, North London</p>
    </div>`;
}

router.post('/create-checkout-session', async (req, res) => {
  const {
    customerName,
    customerEmail,
    customerPhone,
    vehicleRegistration,
    vehicleMake,
    vehicleModel,
    vehicleData,
    appointmentDate,
    appointmentTime,
    serviceType,
    totalAmount,
    successUrl,
    cancelUrl
  } = req.body || {};

  if (!customerEmail || !vehicleRegistration || !appointmentDate || !appointmentTime) {
    return res.status(400).json({ error: 'Missing required fields: email, vehicleRegistration, appointmentDate, appointmentTime' });
  }

  const amount = totalAmount ? Math.round(parseFloat(totalAmount) * 100) : MOT_PRICE_GBP;
  const bookingId = 'N05-' + Date.now();

  if (!stripe) {
    return res.status(503).json({
      error: 'Stripe not configured',
      hint: 'Set STRIPE_SECRET_KEY and STRIPE_PUBLISHABLE_KEY in .env'
    });
  }

  try {
    const session = await stripe.checkout.sessions.create({
      payment_method_types: ['card'],
      line_items: [{
        price_data: {
          currency: 'gbp',
          product_data: {
            name: serviceType || 'MOT Test',
            description: `${vehicleMake || ''} ${vehicleModel || ''} (${vehicleRegistration}) — ${appointmentDate} at ${appointmentTime}`,
            images: []
          },
          unit_amount: amount
        },
        quantity: 1
      }],
      mode: 'payment',
      success_url: successUrl || `${req.protocol}://${req.get('host')}/mot-booking.html?success=1&booking=${bookingId}`,
      cancel_url: cancelUrl || `${req.protocol}://${req.get('host')}/mot-booking.html?cancel=1`,
      customer_email: customerEmail,
      metadata: {
        bookingId,
        customerName: customerName || '',
        customerPhone: customerPhone || '',
        vehicleRegistration: String(vehicleRegistration),
        vehicleMake: vehicleMake || '',
        vehicleModel: vehicleModel || '',
        appointmentDate: String(appointmentDate),
        appointmentTime: String(appointmentTime),
        serviceType: serviceType || 'MOT Test',
        totalAmount: String(amount / 100)
      }
    });

    res.json({
      sessionId: session.id,
      publishableKey: stripePublishableKey,
      bookingId,
      url: session.url
    });
  } catch (err) {
    console.error('Stripe checkout error:', err);
    res.status(500).json({ error: 'Could not create checkout session', detail: err.message });
  }
});

async function stripeWebhookHandler(req, res) {
  const sig = req.headers['stripe-signature'];
  if (!process.env.STRIPE_WEBHOOK_SECRET || !sig || !stripe) {
    return res.status(400).send('Webhook secret not configured');
  }
  let event;
  try {
    event = stripe.webhooks.constructEvent(req.body, sig, process.env.STRIPE_WEBHOOK_SECRET);
  } catch (e) {
    return res.status(400).send(`Webhook Error: ${e.message}`);
  }
  if (event.type === 'checkout.session.completed') {
    const session = event.data.object;
    await notifyAndEmail(session.metadata, session.customer_email);
  }
  res.json({ received: true });
}

router.post('/confirm-booking', async (req, res) => {
  const metadata = req.body.metadata || req.body;
  const email = req.body.customer_email || metadata.customerEmail || metadata.customer_email;
  if (!email || !metadata.vehicleRegistration) {
    return res.status(400).json({ error: 'Missing email or vehicleRegistration' });
  }
  const date = metadata.appointmentDate;
  const time = metadata.appointmentTime;
  if (date && time) {
    const booked = readBookedSlots()[date] || [];
    if (booked.includes(time)) {
      return res.status(409).json({ error: 'This time slot has already been booked. Please choose another date or time.' });
    }
  }
  try {
    await notifyAndEmail(metadata, email);
    res.json({ ok: true });
  } catch (err) {
    console.error('Confirm booking error:', err);
    res.status(500).json({ error: err.message });
  }
});

// ── VoodooSMS helper ────────────────────────────────────────────────────────
async function sendSms(to, message) {
  const apiKey = process.env.VOODOO_API_KEY;
  const sender = process.env.VOODOO_SENDER || 'NO5Tyres';
  if (!apiKey || !to) return;

  // Normalise to UK format without +
  let n = to.replace(/\D/g, '');
  if (n.startsWith('0')) n = '44' + n.slice(1);
  else if (!n.startsWith('44')) n = '44' + n;

  try {
    const fetch = (await import('node-fetch').catch(() => null));
    const fetchFn = fetch ? fetch.default || fetch : null;
    if (!fetchFn) {
      // Fallback: use https module
      return new Promise((resolve) => {
        const body = JSON.stringify({ to: n, from: sender, msg: message });
        const req = https.request({
          hostname: 'www.voodoosms.com',
          path: '/vapi/sms/sendSMS',
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Authorization': apiKey, 'Content-Length': Buffer.byteLength(body) }
        }, (res) => {
          let data = '';
          res.on('data', chunk => data += chunk);
          res.on('end', () => { console.log('VoodooSMS:', data); resolve(); });
        });
        req.on('error', (e) => { console.error('VoodooSMS error:', e.message); resolve(); });
        req.write(body);
        req.end();
      });
    }
    const res = await fetchFn('https://www.voodoosms.com/vapi/sms/sendSMS', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': apiKey },
      body: JSON.stringify({ to: n, from: sender, msg: message }),
    });
    const json = await res.json();
    console.log('VoodooSMS result:', json);
  } catch (e) {
    console.error('VoodooSMS error:', e.message);
  }
}

async function notifyAndEmail(metadata, email) {
  const {
    bookingId,
    customerName,
    customerPhone,
    vehicleRegistration,
    vehicleMake,
    vehicleModel,
    appointmentDate,
    appointmentTime,
    serviceType,
    totalAmount
  } = metadata || {};

  const data = {
    bookingId: bookingId || 'N05-' + Date.now(),
    customerName: customerName || 'Customer',
    customerEmail: email,
    customerPhone: customerPhone || '-',
    vehicleRegistration: vehicleRegistration || '-',
    vehicleMake: vehicleMake || '-',
    vehicleModel: vehicleModel || '',
    appointmentDate: appointmentDate || '-',
    appointmentTime: appointmentTime || '-',
    serviceType: serviceType || 'MOT Test',
    totalAmount: totalAmount || '19.00'
  };

  if (data.appointmentDate && data.appointmentDate !== '-' && data.appointmentTime && data.appointmentTime !== '-') {
    saveBookedSlot(data.appointmentDate, data.appointmentTime);
  }

  const adminEmail = process.env.ADMIN_EMAIL;

  if (bot && telegramChatId) {
    const msg = `🛞 *New MOT Booking*

📋 *Booking ID:* ${data.bookingId}
👤 *Customer:* ${data.customerName}
📧 *Email:* ${data.customerEmail}
📱 *Phone:* ${data.customerPhone}

🚗 *Vehicle:* ${data.vehicleMake} ${data.vehicleModel}
🔢 *Registration:* ${data.vehicleRegistration}

📅 *Date:* ${data.appointmentDate}
🕐 *Time:* ${data.appointmentTime}
🔧 *Service:* ${data.serviceType}
💰 *Amount:* £${data.totalAmount}`;

    await bot.sendMessage(telegramChatId, msg, { parse_mode: 'Markdown' });
  }

  if (adminEmail && transporter) {
    const adminHtml = `
      <h2>New MOT/Service Booking</h2>
      <table style="border-collapse:collapse;">
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Booking ID</strong></td><td style="padding:8px;border:1px solid #eee;">${data.bookingId}</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Customer</strong></td><td style="padding:8px;border:1px solid #eee;">${data.customerName}</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Email</strong></td><td style="padding:8px;border:1px solid #eee;">${data.customerEmail}</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Phone</strong></td><td style="padding:8px;border:1px solid #eee;">${data.customerPhone}</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Vehicle</strong></td><td style="padding:8px;border:1px solid #eee;">${data.vehicleMake} ${data.vehicleModel} (${data.vehicleRegistration})</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Date & Time</strong></td><td style="padding:8px;border:1px solid #eee;">${data.appointmentDate} at ${data.appointmentTime}</td></tr>
        <tr><td style="padding:8px;border:1px solid #eee;"><strong>Service</strong></td><td style="padding:8px;border:1px solid #eee;">${data.serviceType}</td></tr>
      </table>
    `;
    await transporter.sendMail({
      from: process.env.SMTP_FROM || '"N05 Tyre & MOT" <noreply@no5mot.co.uk>',
      to: adminEmail,
      subject: `New booking: ${data.bookingId} — ${data.customerName}`,
      html: adminHtml
    });
  }

  const html = loadEmailTemplate(data);
  await transporter.sendMail({
    from: process.env.SMTP_FROM || '"N05 Tyre & MOT" <noreply@no5mot.co.uk>',
    to: email,
    subject: `MOT Booking Confirmed - ${data.bookingId}`,
    html
  });

  // SMS confirmation to customer
  if (data.customerPhone && data.customerPhone !== '-') {
    const smsText = `Hi ${data.customerName}, your ${data.serviceType} at N05 Tyre & MOT is confirmed for ${data.appointmentDate} at ${data.appointmentTime}. Ref: ${data.bookingId}. Questions? Call 07895 859505.`;
    await sendSms(data.customerPhone, smsText).catch(() => {});
  }
}

router.get('/available-slots', (req, res) => {
  const date = (req.query.date || '').toString().trim();
  if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(date)) {
    return res.json({ available: [] });
  }
  const d = new Date(date + 'T12:00:00');
  const day = d.getDay();
  if (day === 0) {
    return res.json({ available: [] });
  }
  const booked = readBookedSlots()[date] || [];
  const available = ALL_SLOTS.filter((t) => !booked.includes(t));
  res.json({ available });
});

router.post('/mot-notify', async (req, res) => {
  const { email, vrm, motDueDate } = req.body || {};
  if (!email || !vrm) {
    return res.status(400).json({ error: 'Email and registration (vrm) required' });
  }
  try {
    if (bot && telegramChatId) {
      await bot.sendMessage(telegramChatId,
        `🔔 *MOT Reminder signup*\n📧 ${email}\n🚗 VRM: ${String(vrm).toUpperCase()}${motDueDate ? '\n📅 MOT due: ' + motDueDate : ''}`,
        { parse_mode: 'Markdown' });
    }
    return res.json({ ok: true, message: "We'll notify you when your MOT is due." });
  } catch (e) {
    console.error('MOT notify error:', e);
    return res.status(500).json({ error: 'Could not save notification' });
  }
});

router.get('/config', (req, res) => {
  res.json({
    stripePublishableKey: stripePublishableKey || '',
    hasTelegram: !!bot && !!telegramChatId
  });
});

module.exports = router;
module.exports.stripeWebhookHandler = stripeWebhookHandler;
