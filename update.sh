#!/bin/bash
# update.sh — Mise à jour du Ski-Club Manager
# Usage : cd /opt/ski-club-manager && sudo bash update.sh
set -e

APP_DIR="/opt/ski-club-manager"
cd "$APP_DIR"

echo "→ Pull depuis GitHub..."
git pull origin main

echo "→ Dépendances Composer..."
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

echo "→ Migrations..."
sudo -u www-data php8.3 artisan migrate --force

echo "→ Cache..."
sudo -u www-data php8.3 artisan config:cache
sudo -u www-data php8.3 artisan route:cache
sudo -u www-data php8.3 artisan view:cache

echo "→ Redémarrage du worker..."
systemctl restart skiclub-worker

echo ""
echo "✓ Mise à jour terminée — https://skiclub.ifotech.ch"
