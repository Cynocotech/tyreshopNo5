/**
 * N05 Tyre & MOT - Booking API Server
 * Handles: vehicle lookup (checkcardetails.co.uk), MOT booking, Stripe, Telegram, email
 */
const path = require('path');
const fs = require('fs');
// Load root .env first, then server/.env (override) so server vars take precedence
require('dotenv').config({ path: path.join(__dirname, '../.env') });
require('dotenv').config({ path: path.join(__dirname, '.env'), override: true });
// Fallback: if Telegram vars still missing, parse .env files directly (fixes dotenv path issues)
if (!process.env.TELEGRAM_BOT_TOKEN || !process.env.TELEGRAM_CHAT_ID) {
  const parseEnv = (content) => {
    for (const line of content.split(/\r?\n/)) {
      let m = line.match(/^TELEGRAM_BOT_TOKEN\s*=\s*(.+)$/);
      if (m) process.env.TELEGRAM_BOT_TOKEN = m[1].replace(/\s*#.*$/, '').replace(/^["']|["']$/g, '').trim();
      m = line.match(/^TELEGRAM_CHAT_ID\s*=\s*(.+)$/);
      if (m) process.env.TELEGRAM_CHAT_ID = m[1].replace(/\s*#.*$/, '').replace(/^["']|["']$/g, '').trim();
    }
  };
  for (const envPath of [path.join(__dirname, '.env'), path.join(__dirname, '../.env')]) {
    try {
      parseEnv(fs.readFileSync(envPath, 'utf8'));
      if (process.env.TELEGRAM_BOT_TOKEN && process.env.TELEGRAM_CHAT_ID) break;
    } catch (_) {}
  }
}
// Debug: verify Telegram vars loaded (helps if "skipped" appears)
const hasToken = !!(process.env.TELEGRAM_BOT_TOKEN || '').trim();
const hasChatId = !!(process.env.TELEGRAM_CHAT_ID || '').trim();
if (!hasToken || !hasChatId) {
  console.log('Tip: Add TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID to server/.env (or root .env)');
  console.log('DEBUG env:', {
    TELEGRAM_BOT_TOKEN: process.env.TELEGRAM_BOT_TOKEN ? `set (${String(process.env.TELEGRAM_BOT_TOKEN).length} chars)` : 'undefined',
    TELEGRAM_CHAT_ID: process.env.TELEGRAM_CHAT_ID || 'undefined'
  });
}
const express = require('express');
const cors = require('cors');

const vehicleRouter = require('./routes/vehicle');
const bookingRouter = require('./routes/booking');
const servicesHandler = require('./routes/services');

const app = express();
const PORT = process.env.PORT || 3001;

app.use(cors({ origin: true }));

// Serve services from Laravel SQLite (before static so it overrides data/services.json)
app.get('/data/services.json', servicesHandler);

// Serve front page and static assets from admin/public (same as production)
app.use(express.static(path.join(__dirname, '../admin/public')));

// Stripe webhook needs raw body - mount BEFORE express.json()
const { stripeWebhookHandler } = require('./routes/booking');
app.post('/api/booking/webhook/stripe', express.raw({ type: 'application/json' }), stripeWebhookHandler);
app.use(express.json());

app.use('/api/vehicle', vehicleRouter);
app.use('/api/booking', bookingRouter);

app.get('/api/health', (req, res) => res.json({ ok: true }));

app.listen(PORT, () => {
  console.log(`N05 MOT API running on http://localhost:${PORT}`);
  // Debug: verify env vars (helps when "skipped" despite .env having them)
  const token = process.env.TELEGRAM_BOT_TOKEN || '';
  const chatId = process.env.TELEGRAM_CHAT_ID || '';
  console.log('Telegram env:', { hasToken: !!token, tokenLen: token.length, hasChatId: !!chatId, chatId: chatId || '(empty)' });
  const { initAdminBot } = require('./telegram-admin');
  initAdminBot({ token, chatId });
});
