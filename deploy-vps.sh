#!/bin/bash
# deploy-vps.sh
# Script de déploiement initial du Ski-Club Manager sur le VPS ifotech
# Usage : sudo bash deploy-vps.sh
#
# Ce script :
#  1. Installe PHP 8.3, Composer, MySQL
#  2. Clone le repo dans /opt/ski-club-manager
#  3. Configure l'environnement Laravel (.env)
#  4. Lance les migrations et optimisations
#  5. Copie la config Nginx et émet le certificat SSL
#  6. Active le worker systemd (envoi d'emails en queue)
#
# Prérequis :
#  - DNS A record skiclub.ifotech.ch → IP de ce VPS
#  - Fichier .env.production présent (ou adapter les variables ci-dessous)
set -e

DOMAIN="skiclub.ifotech.ch"
EMAIL="sebastien.gerber@ifotech.ch"
APP_DIR="/opt/ski-club-manager"
REPO="https://github.com/Seb779/ski-club-manager.git"

# ── Couleurs ──────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo ""
echo "======================================"
echo " ifotech VPS — Ski-Club Manager"
echo "======================================"

# ── 1. PHP 8.3 + extensions Laravel ──────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Installation PHP 8.3...${NC}"
apt update -q
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update -q
apt install -y \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-bcmath php8.3-tokenizer php8.3-intl \
    php8.3-gd unzip git curl

systemctl enable php8.3-fpm
systemctl start php8.3-fpm
echo "  ✓ PHP $(php8.3 -r 'echo PHP_VERSION;') installé"

# ── 2. Composer ───────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Installation Composer...${NC}"
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php8.3
    mv composer.phar /usr/local/bin/composer
fi
echo "  ✓ Composer $(composer --version --no-ansi | awk '{print $3}')"

# ── 3. MySQL ──────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Installation et configuration MySQL...${NC}"
apt install -y mysql-server

DB_PASS=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 20)

mysql -e "CREATE DATABASE IF NOT EXISTS skiclub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'skiclub'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON skiclub.* TO 'skiclub'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "  ✓ Base de données 'skiclub' créée"
echo -e "  ${YELLOW}⚠  Mot de passe MySQL généré : ${DB_PASS}${NC}"
echo "     (conservez-le, il sera écrit dans le .env)"

# ── 4. Cloner le repo ─────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Clone / mise à jour du repo...${NC}"
if [ -d "$APP_DIR/.git" ]; then
    cd "$APP_DIR"
    git pull origin main
    echo "  ✓ Repo mis à jour"
else
    git clone "$REPO" "$APP_DIR"
    echo "  ✓ Repo cloné dans $APP_DIR"
fi

chown -R www-data:www-data "$APP_DIR"

# ── 5. Dépendances PHP ────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Installation des dépendances Composer...${NC}"
cd "$APP_DIR"
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# ── 6. Fichier .env ───────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Configuration .env...${NC}"
if [ ! -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
fi

# Écrire les valeurs automatiques
sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|"             "$APP_DIR/.env"
sed -i "s|APP_ENV=.*|APP_ENV=production|"                     "$APP_DIR/.env"
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|"                      "$APP_DIR/.env"
sed -i "s|DB_DATABASE=.*|DB_DATABASE=skiclub|"                "$APP_DIR/.env"
sed -i "s|DB_USERNAME=.*|DB_USERNAME=skiclub|"                "$APP_DIR/.env"
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|"             "$APP_DIR/.env"

echo ""
echo -e "  ${YELLOW}⚠  Configurer manuellement le SMTP dans .env :${NC}"
echo "     nano $APP_DIR/.env"
echo "     (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD)"
echo ""
read -p "  Appuyez sur Entrée après avoir configuré le SMTP pour continuer..."

# ── 7. Clé, migrations, optimisations ────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Initialisation Laravel...${NC}"
cd "$APP_DIR"

sudo -u www-data php8.3 artisan key:generate
sudo -u www-data php8.3 artisan migrate --force
sudo -u www-data php8.3 artisan db:seed --force
sudo -u www-data php8.3 artisan config:cache
sudo -u www-data php8.3 artisan route:cache
sudo -u www-data php8.3 artisan view:cache
sudo -u www-data php8.3 artisan storage:link

chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "  ✓ Laravel initialisé"

# ── 8. Config Nginx ───────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Configuration Nginx...${NC}"
cp "$APP_DIR/nginx/${DOMAIN}" "/etc/nginx/sites-available/${DOMAIN}"
ln -sf "/etc/nginx/sites-available/${DOMAIN}" "/etc/nginx/sites-enabled/${DOMAIN}"

nginx -t
systemctl reload nginx
echo "  ✓ Nginx configuré pour ${DOMAIN}"

# ── 9. Certificat SSL ─────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}→ Certificat Let's Encrypt...${NC}"
certbot --nginx --non-interactive --agree-tos -m "$EMAIL" -d "$DOMAIN"
echo "  ✓ SSL activé"

# ── 10. Service systemd (queue worker pour emails) ────────────────────────────
echo ""
echo -e "${GREEN}→ Service queue worker...${NC}"
cp "$APP_DIR/systemd/skiclub-worker.service" "/etc/systemd/system/skiclub-worker.service"
systemctl daemon-reload
systemctl enable skiclub-worker
systemctl start skiclub-worker
echo "  ✓ Queue worker actif"

# ── Résumé ────────────────────────────────────────────────────────────────────
echo ""
echo "======================================"
echo " Déploiement terminé ✓"
echo "======================================"
echo ""
echo "  URL     → https://${DOMAIN}"
echo "  App     → ${APP_DIR}"
echo "  DB      → skiclub / skiclub@localhost"
echo "  Logs    → /var/log/nginx/skiclub.*.log"
echo "  Worker  → journalctl -u skiclub-worker -f"
echo ""
echo " Prochaines mises à jour :"
echo "  cd ${APP_DIR} && sudo bash update.sh"
echo ""
