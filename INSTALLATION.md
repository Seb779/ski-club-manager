# Ski-Club Manager — Guide d'installation

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL 8 (ou MariaDB 10.6+)

---

## Installation locale (développement)

```bash
# 1. Installer les dépendances PHP
composer install

# 2. Installer les dépendances JS
npm install

# 3. Copier et configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans .env
# DB_DATABASE=skiclub
# DB_USERNAME=...
# DB_PASSWORD=...

# 5. Créer la base de données et lancer les migrations
php artisan migrate

# 6. (Optionnel) Charger les données de démonstration
php artisan db:seed

# 7. Lancer les serveurs de développement
php artisan serve &
npm run dev
```

Accès : http://localhost:8000

---

## Déploiement Infomaniak

### Hébergement recommandé
**Hébergement Web Pro** (PHP 8.3, MySQL inclus) ou **VPS Cloud** pour plus de contrôle.

### Étapes

#### 1. Préparer l'hébergement Infomaniak

Dans la console Infomaniak (manager.infomaniak.com) :

1. Créer un hébergement web avec **PHP 8.3**
2. Créer une **base de données MySQL** — noter host, nom, user, mot de passe
3. Configurer le **SMTP** : Aller dans "Emails" → créer une adresse (ex: noreply@votre-skiclub.ch)

#### 2. Configurer le `.env` de production

```env
APP_NAME="Ski-Club Manager"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.votre-skiclub.ch

DB_CONNECTION=mysql
DB_HOST=<host_fourni_par_infomaniak>
DB_PORT=3306
DB_DATABASE=<nom_bdd>
DB_USERNAME=<user_bdd>
DB_PASSWORD=<mot_de_passe>

MAIL_MAILER=smtp
MAIL_HOST=mail.infomaniak.com
MAIL_PORT=587
MAIL_USERNAME=noreply@votre-skiclub.ch
MAIL_PASSWORD=<mot_de_passe_email>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@votre-skiclub.ch"
MAIL_FROM_NAME="Ski-Club"
```

#### 3. Build et upload

```bash
# Sur votre machine locale :
npm run build         # compile les assets (génère public/build/)
composer install --no-dev --optimize-autoloader

# Uploader tout le projet via FTP/SFTP ou Git
# Le dossier "public/" doit pointer sur la racine web d'Infomaniak
```

#### 4. Configuration du document root

Dans le manager Infomaniak, pointer le **document root** vers le sous-dossier `/public` de votre projet.

Ou créer un `.htaccess` à la racine :
```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

#### 5. Migrations en production

Via SSH (disponible sur les hébergements Infomaniak) :
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ou via le **gestionnaire de tâches** Infomaniak pour planifier les commandes.

#### 6. Storage link

```bash
php artisan storage:link
```

---

## Structure des modules

| Module       | URL               | Description                                    |
|-------------|-------------------|------------------------------------------------|
| Dashboard   | `/`               | Vue d'ensemble, stats saison                   |
| Membres     | `/membres`        | CRUD membres, familles, enfants                |
| Cotisations | `/cotisations`    | Gestion et envoi des cotisations (email/postal)|
| Groupes     | `/groupes`        | Cours avec moniteurs, gestion des enfants      |
| Courses     | `/courses`        | Concours, chronos, classements par catégorie   |
| Courriers   | `/courriers`      | Communications club (email + postal)           |
| Paramètres  | `/saisons`        | Saisons, tarifs, catégories de course          |

---

## Évolutions prévues (v2)

- [ ] Authentification (login admin + portail membre en lecture seule)
- [ ] Export Excel des membres et cotisations
- [ ] QR-code sur les cotisations pour paiement TWINT/e-banking
- [ ] Application mobile pour saisie des chronos hors-ligne
- [ ] Intégration Swiss Ski (licences)
