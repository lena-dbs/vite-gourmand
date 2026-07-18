# Vite & Gourmand

Application web de commande de menus traiteur pour Julie et José Santos, traiteurs artisanaux à Bordeaux depuis 1999.

**URL de production** : https://vite-gourmand-old-lagoon-1903.fly.dev/

## Stack technique

| Couche | Technologie |
|---|---|
| Front-end | HTML5, CSS3, JavaScript vanilla |
| Back-end | PHP 8.2, architecture MVC maison |
| BDD relationnelle | MySQL 8.0 (PDO) |
| BDD NoSQL | MongoDB 6.0 |
| Serveur | Apache 2.4 |
| Déploiement | Docker + fly.io |

## Prérequis

- PHP 8.1+ avec extensions `pdo_mysql`, `mbstring`
- MySQL 8.0+ ou MariaDB 10.5+
- Composer 2.x
- Apache avec `mod_rewrite` activé
- MongoDB 6.0+ (pour les statistiques admin)
- Extension PHP MongoDB (`pecl install mongodb`)

## Installation locale (XAMPP)

### 1. Cloner le dépôt

```bash
git clone https://github.com/lena-dbs/vite-gourmand.git
cd vite-gourmand
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Créer la base de données

Importer le fichier SQL dans MySQL :

```bash
mysql -u root -p < database/database.sql
```

Ou via phpMyAdmin :
1. Créer une base de données `vite_gourmand`
2. Importer `database/database.sql`

### 4. Configurer les variables d'environnement

Créer un fichier `.env` à la racine du projet :

```env
DB_HOST=localhost
DB_NAME=vite_gourmand
DB_USER=root
DB_PASS=
DB_PORT=3306

MONGO_URI=mongodb://localhost:27017

APP_URL=http://localhost
```

### 5. Configurer Apache

Le `DocumentRoot` doit pointer vers le dossier `public/` du projet.

**XAMPP** : dans `httpd-vhosts.conf` :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/vite-gourmand/public"
    <Directory "C:/xampp/htdocs/vite-gourmand/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

S'assurer que `mod_rewrite` est activé dans `httpd.conf`.

### 6. Lancer l'application

Démarrer Apache et MySQL depuis le panneau XAMPP, puis accéder à :

```
http://localhost
```

## Déploiement sur fly.io

### 1. Installer la CLI fly.io

```bash
curl -L https://fly.io/install.sh | sh
fly auth login
```

### 2. Créer l'application

```bash
fly launch
```

### 3. Configurer les secrets

```bash
fly secrets set DB_HOST=your-db-host DB_NAME=vite_gourmand DB_USER=user DB_PASS=password DB_PORT=3306 MONGO_URI=mongodb+srv://... APP_URL=https://your-app.fly.dev
```

### 4. Déployer

```bash
fly deploy
```

L'application est containerisée via le `Dockerfile` inclus (PHP 8.2 + Apache + mod_rewrite).

## Structure du projet

```
vite-gourmand/
├── app/
│   ├── Controllers/       # 9 contrôleurs (Admin, Auth, Commande, Contact, Employe, Home, Legal, Menu, User)
│   ├── Models/            # 3 modèles (Commande, Menu, User)
│   └── Views/             # Templates PHP organisés par section
│       ├── admin/         # Vues espace administrateur
│       ├── auth/          # Connexion, inscription, reset password
│       ├── commande/      # Formulaire de commande
│       ├── contact/       # Page contact
│       ├── employe/       # Vues espace employé
│       ├── errors/        # Pages d'erreur (404)
│       ├── home/          # Page d'accueil
│       ├── layouts/       # Header et footer communs
│       ├── legal/         # Mentions légales, CGV
│       ├── menus/         # Liste et détail des menus
│       └── user/          # Espace client (dashboard, commandes, profil)
├── config/
│   └── config.php         # Configuration BDD et constantes
├── core/
│   ├── autoload.php       # Chargement automatique des classes
│   ├── Controller.php     # Contrôleur de base (render, CSRF, redirect)
│   ├── Database.php       # Singleton PDO avec auto-reconnexion
│   ├── Model.php          # Modèle de base
│   ├── MongoStats.php     # Client MongoDB pour les statistiques
│   └── Router.php         # Routeur URL → Contrôleur
├── database/
│   └── database.sql       # Schéma + données de démonstration
├── public/
│   ├── index.php          # Front controller (point d'entrée unique)
│   ├── .htaccess          # Réécriture URL Apache
│   └── assets/
│       ├── css/style.css  # Feuille de style unique
│       ├── js/main.js     # JavaScript (filtres AJAX, menu burger, animations)
│       └── images/        # Photos des menus et illustrations
├── .env                   # Variables d'environnement (non versionné)
├── composer.json          # Dépendances PHP (mongodb, phpdotenv)
├── Dockerfile             # Image Docker pour le déploiement
├── fly.toml               # Configuration fly.io
└── README.md              # Ce fichier
```

## Identifiants de démonstration

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | jose@vitegourmand.fr | password |
| Employé | julie@vitegourmand.fr | password |
| Utilisateur | test@email.fr | password |

## Sécurité

- Tokens CSRF sur tous les formulaires POST
- Mots de passe hachés en bcrypt (`PASSWORD_BCRYPT`)
- Requêtes SQL préparées (PDO) contre les injections
- Protection XSS via `htmlspecialchars()` sur toutes les sorties
- Headers HTTP de sécurité (CSP, X-Frame-Options, HSTS, etc.)
- Session sécurisée (httponly, samesite Strict, timeout 30 min)
- Variables d'environnement via `.env` (phpdotenv)

## Licence

Projet réalisé dans le cadre du TP Développeur Web et Web Mobile — Studi / FastDev.
