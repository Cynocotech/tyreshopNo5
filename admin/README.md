# N05 Admin Panel

Laravel admin for managing the N05 Tyre & MOT website content: services, categories, site settings, FAQs, and areas.

## Setup

```bash
cd admin
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

## Access

- **URL**: `/admin` (redirects to login if not authenticated)
- **Default admin**: `admin@example.com` (password from `User::factory()`)
- Create a new user via `/register` or run: `php artisan tinker` → `User::factory()->create(['email'=>'you@example.com'])` then `php artisan make:password` or set manually.

## Features

| Section | Description |
|---------|-------------|
| **Dashboard** | Overview and export to site |
| **Services** | CRUD for services (title, price, category, icon, keywords) |
| **Categories** | Service categories with sort order |
| **Site Settings** | Address, phone, logo, opening hours, hero/footer text |
| **FAQs** | FAQ questions and answers |
| **Areas** | Areas served (footer "areas served") |
| **Export to Site** | Writes `../data/services.json` for the Node.js site |

## Export

- **Web**: Click "Export to Site" in the sidebar or from the dashboard.
- **CLI**: `php artisan no5:export-services`

Export writes to `../data/services.json` relative to the admin directory (i.e. `NO5/data/services.json`), which the main site fetches as `/data/services.json`.

## Data Flow

1. Edit content in the admin panel.
2. Click "Export to Site" to write `data/services.json`.
3. The main site (Node.js backend + static HTML) reads from `data/services.json`.
4. Site settings and FAQs are stored in the Laravel database; you can extend export to generate static config if needed.

## Database

Uses SQLite by default. To use MySQL/PostgreSQL, update `.env` and run migrations.
