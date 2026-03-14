# N05 Tyre & MOT — Palmers Green Service Centre

MOT and tyre booking system with vehicle lookup (Check Car Details), Stripe payments, Telegram admin notifications, and customer email confirmations.

## Project Structure

```
NO5/
├── admin/
│   ├── public/             ← All public files (set as document root)
│   │   ├── index.html      # Front page
│   │   ├── mot-booking.html
│   │   ├── css/
│   │   ├── js/
│   │   └── blog/
│   └── ...                 # Laravel app (admin panel, API)
├── server/
│   ├── index.js            # Express API (local dev)
│   ├── routes/
│   │   ├── vehicle.js      # Check Car Details proxy
│   │   └── booking.js      # Stripe, Telegram, email
│   └── views/
│       └── booking-email.html   # Customer confirmation email template
└── .env.example
```

**Deployment:** Point document root to `admin/public`. See `DEPLOYMENT-CPANEL.md`.

## Setup

1. **Install dependencies**
   ```bash
   cd server && npm install
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` with your values:

   - **Check Car Details**: Sign up at [api.checkcardetails.co.uk/auth/register](https://api.checkcardetails.co.uk/auth/register) and add your API key.
   - **Stripe**: Get keys from [dashboard.stripe.com/apikeys](https://dashboard.stripe.com/apikeys).
   - **Telegram**: Bot token and admin chat ID for booking notifications.
   - **SMTP**: Email credentials for sending confirmation emails to customers.

3. **Run the server**
   ```bash
   cd server && npm start
   ```
   Open http://localhost:3001 — serves static files and API.

4. **Stripe webhook** (for production): Point `checkout.session.completed` to  
   `https://your-domain.com/api/booking/webhook/stripe` and set `STRIPE_WEBHOOK_SECRET`.

## Features

- **Vehicle lookup** via Check Car Details API (VRM → full vehicle details)
- **MOT booking** with date/time, service type
- **Stripe Checkout** for secure card payments
- **Telegram** notifications to admin on new bookings
- **Email confirmation** to customer using `server/views/booking-email.html`

## API Endpoints

- `GET /api/vehicle/lookup?vrm=AB12CDE` — Vehicle details
- `POST /api/booking/create-checkout-session` — Start Stripe payment
- `POST /api/booking/confirm-booking` — Trigger Telegram + email (e.g. from success page)
- `POST /api/booking/webhook/stripe` — Stripe webhook (raw body)
