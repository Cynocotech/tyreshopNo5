# N05 Tyre & MOT — cPanel Deployment Guide

**Single Laravel app** — Front page, booking API, and admin panel in one PHP deployment. No Node.js required.

---

## One place, one document root

**All public files live in `admin/public`.** Point your domain’s document root there and nothing else.

```
admin/public/        ← Set this as your document root
├── index.php        ← Laravel entry
├── index.html       ← Front page
├── mot-booking.html
├── css/
├── js/
├── blog/
└── ...
```

**cPanel → Domains → Document Root** → set to:

```
/home/yourusername/tyre/admin/public
```

(or the full path to your repo’s `admin/public` folder)

**Updates:** `git pull` is enough — no deploy script. Files update in place.

---

## Quick Install (recommended)

From cPanel Terminal, after cloning the repo:

```bash
cd ~/tyre
bash install-cpanel.sh
```

This script will:
- Find Composer and npm (including cPanel Node.js)
- Create .env from .env.example
- Install PHP dependencies (composer)
- Run migrations and caches
- Build frontend assets (optional; app works without npm using CDN)
- Set permissions

Then set your domain’s document root to `~/tyre/admin/public`.

---

## After git pull (front page updates)

```bash
cd ~/tyre
git pull
```

Hard refresh (Ctrl+Shift+R) or clear Cloudflare/cache if needed.

---

## Sync services to production

To push your local services and categories to the live site:

```bash
cd ~/tyre   # or your project root
bash push-services.sh
```

This exports services from your local database to `data/services.json` and prints instructions to upload and run the import on the server. On the server:

```bash
cd ~/tyre/admin
php artisan no5:import-services ../data/services.json
```

See `push-services.sh` for upload options (SSH, cPanel File Manager, or Git).

---

## Deployment Steps (manual)

### 1. Upload Files

- Upload the entire `admin` folder to your hosting (e.g. via FTP/SFTP or File Manager).
- Or: Upload only the contents of `admin/`, and ensure the web root points to the `public` subfolder.

### 2. Point Document Root

- In cPanel → **Domains** or **Addon Domains** → set **Document Root** to:
  ```
  /home/yourusername/tyre/admin/public
  ```
  (Adjust path to match your repo location.)

### 3. Create .env

Copy `.env.example` to `.env` and configure:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://no5mot.co.uk

# Copy from server/.env for production (or use existing values)
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLISHABLE_KEY=pk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

CHECK_CAR_DETAILS_API_KEY=your_key
# Or: CHECK_CAR_DETAILS_MOCK=1 for fake vehicle data

TELEGRAM_BOT_TOKEN=...
TELEGRAM_CHAT_ID=...

MAIL_MAILER=smtp
MAIL_HOST=smtppro.zoho.eu
MAIL_PORT=587
MAIL_USERNAME=booking@no5tyreandmot.co.uk
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="booking@no5tyreandmot.co.uk"
MAIL_FROM_NAME="N05 Tyre & MOT"
ADMIN_EMAIL=booking@no5tyreandmot.co.uk
```

### 4. Run Commands (SSH or cPanel Terminal)

```bash
cd /path/to/admin

composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build frontend assets for admin panel
npm ci
npm run build
```

### 5. File Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Stripe Webhook (Production)

1. Create webhook in Stripe Dashboard → **Developers** → **Webhooks**.
2. URL: `https://no5mot.co.uk/api/booking/webhook/stripe`
3. Events: `checkout.session.completed`
4. Copy the signing secret into `STRIPE_WEBHOOK_SECRET`.

---

## URLs After Deployment

| Page            | URL                        |
|-----------------|----------------------------|
| Front page      | `https://no5mot.co.uk/`    |
| MOT booking     | `https://no5mot.co.uk/mot-booking.html` |
| Admin panel     | `https://no5mot.co.uk/admin` |
| Login           | `https://no5mot.co.uk/login` |

---

## Local Development (PHP Only)

From project root:

```bash
cd admin
php artisan serve --port=8000
```

Then open:
- Front: http://localhost:8000/
- Admin: http://localhost:8000/admin
- Login: http://localhost:8000/login (admin@example.com / password)

---

## Alternative: Use public_html (when you can't change Document Root)

`.htaccess` **cannot change the document root**—that's set by the server. Instead, copy the public files into `public_html` and use a bootstrap `index.php` that loads Laravel from `~/tyre/admin`.

### Option 1: Run the deploy script (recommended)

After `install-cpanel.sh`:

```bash
cd ~/tyre
bash deploy-to-public-html.sh
```

This copies everything from `admin/public` into `public_html`, uses a custom `index.php` that points to `~/tyre/admin`, and creates the storage symlink.

### Option 2: Manual copy

1. Copy all files from `admin/public` into `public_html`.
2. Replace `public_html/index.php` with `admin/public/index-for-public-html.php`.
3. Create symlink: `ln -sf ~/tyre/admin/storage/app/public ~/public_html/storage`

---

## Troubleshooting

- **500 error**: Check `storage/logs/laravel.log` and file permissions.
- **Blank page**: Set `APP_DEBUG=true` temporarily (never leave this on in production).
- **Booking emails not sent**: Verify SMTP settings in `.env` and run `php artisan config:clear`.
