#!/bin/bash
# Push local services to the live site
# Run from project root: bash push-services.sh
#
# This exports services from your local database to data/services.json,
# then prints instructions to upload and import on the server.

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "=== Exporting services from local database ==="
cd admin
php artisan no5:export-services
cd ..

DATA_FILE="data/services.json"
if [ ! -f "$DATA_FILE" ]; then
  echo "Error: Export failed, $DATA_FILE not found"
  exit 1
fi

COUNT=$(grep -c '"id":' "$DATA_FILE" 2>/dev/null || echo 0)
echo ""
echo "Exported to $DATA_FILE ($COUNT services)"
echo ""
echo "=== Next: Push to the live site ==="
echo ""
echo "Option A — SSH (if you have terminal access):"
echo "  1. Upload the file:"
echo "     scp $DATA_FILE your-user@your-server:~/tyre/data/"
echo ""
echo "  2. On the server, run:"
echo "     cd ~/tyre/admin"
echo "     php artisan no5:import-services ../data/services.json"
echo ""
echo "Option B — cPanel File Manager:"
echo "  1. Upload $DATA_FILE to ~/tyre/data/ (create data/ folder if needed)"
echo "  2. Open cPanel Terminal and run:"
echo "     cd ~/tyre/admin"
echo "     php artisan no5:import-services ../data/services.json"
echo ""
echo "Option C — Git push (if repo is on server):"
echo "  1. git add $DATA_FILE"
echo "  2. git commit -m 'Sync services'"
echo "  3. git push"
echo "  4. On server: git pull && cd admin && php artisan no5:import-services ../data/services.json"
echo ""
