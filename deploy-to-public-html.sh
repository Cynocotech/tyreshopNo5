#!/bin/bash
# Deploy NO5 public files to public_html (when you can't change Document Root)
# Run from project root: bash deploy-to-public-html.sh
# Requires: repo at ~/tyre, public_html in same home dir

set -e
HOME_DIR="${HOME:-/home/$(whoami)}"
REPO_DIR="$HOME_DIR/tyre"
ADMIN_PUBLIC="$REPO_DIR/admin/public"
PUBLIC_HTML="$HOME_DIR/public_html"

[ -d "$ADMIN_PUBLIC" ] || { echo "Error: $ADMIN_PUBLIC not found"; exit 1; }
[ -d "$PUBLIC_HTML" ] || { echo "Error: $PUBLIC_HTML not found"; exit 1; }

echo "Copying admin/public -> public_html"
rsync -a --delete \
  --exclude='.git' \
  --exclude='index.php' \
  --exclude='storage' \
  "$ADMIN_PUBLIC/" "$PUBLIC_HTML/"

echo "Installing index.php for public_html"
cp "$ADMIN_PUBLIC/index-for-public-html.php" "$PUBLIC_HTML/index.php"

echo "Creating storage symlink (if missing)"
[ -L "$PUBLIC_HTML/storage" ] || ln -sf "$REPO_DIR/admin/storage/app/public" "$PUBLIC_HTML/storage" 2>/dev/null || true

echo "Done. Site should be live at your domain."
