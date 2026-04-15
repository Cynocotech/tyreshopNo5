#!/bin/bash
# N05 Tyre & MOT — cPanel Installer
# Run from project root: bash install-cpanel.sh
# Or from admin folder: bash ../install-cpanel.sh

set -e
ADMIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
[ -f "$ADMIN_DIR/artisan" ] || ADMIN_DIR="$ADMIN_DIR/admin"
[ -f "$ADMIN_DIR/artisan" ] || { echo "Error: admin folder not found"; exit 1; }

cd "$ADMIN_DIR"
echo "Installing N05 in: $ADMIN_DIR"
echo ""

# --- Find Composer ---
COMPOSER=""
if command -v composer &>/dev/null; then
  COMPOSER="composer"
elif [ -f "$HOME/composer.phar" ]; then
  COMPOSER="php $HOME/composer.phar"
else
  echo "Composer not found. Install: curl -sS https://getcomposer.org/installer | php"
  echo "Then move to: mv composer.phar ~/composer.phar"
  exit 1
fi

# --- Find NPM (optional) ---
NPM=""
if command -v npm &>/dev/null; then
  NPM="npm"
elif [ -d "$HOME/nodevenv" ]; then
  # cPanel Node.js: use first available
  for dir in "$HOME"/nodevenv/*/*/bin; do
    if [ -x "$dir/npm" ]; then
      NPM="$dir/npm"
      break
    fi
  done
fi
[ -n "$NPM" ] && echo "Found npm: $NPM" || echo "npm not found (skipping frontend build)"

# --- Step 1: .env ---
echo "[1/6] Setting up .env"
if [ ! -f .env ]; then
  [ -f .env.example ] && cp .env.example .env && echo "  Created .env from .env.example"
else
  echo "  .env exists"
fi

# --- Step 2: Composer ---
echo "[2/6] Installing PHP dependencies"
$COMPOSER install --optimize-autoloader --no-dev 2>/dev/null || \
  $COMPOSER update --no-dev --optimize-autoloader

# --- Step 3: Laravel ---
echo "[3/6] Laravel setup"
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- Step 4: NPM (optional - app works without it, uses CDN) ---
echo "[4/6] Frontend assets"
if [ -n "$NPM" ] && [ -f package.json ]; then
  $NPM ci 2>/dev/null || $NPM install
  $NPM run build
else
  echo "  Skipped (npm not found - app uses CDN assets, no build needed)"
fi

# --- Step 5: Permissions ---
echo "[5/6] Permissions"
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# --- Step 6: Done ---
echo "[6/6] Done!"
echo ""
echo "Next steps:"
echo "  1. Edit .env with Stripe, SMTP, Telegram, etc."
echo "  2. Set document root to: $ADMIN_DIR/public"
echo "  3. Visit your domain: https://yourdomain.com/"
echo "  4. Admin: https://yourdomain.com/admin"
echo ""
