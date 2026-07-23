# Vite & Gourmand

Application web de commande de menus traiteur, réalisée pour Julie et José (traiteurs à Bordeaux depuis 25 ans) dans le cadre du TP Développeur Web et Web Mobile.

URL de prod : https://vite-gourmand-old-lagoon-1903.fly.dev/

## Ce que fait l'appli

Côté visiteur : présentation de l'entreprise et avis clients sur la page d'accueil, liste des menus avec filtres (prix min/max, thème, régime, nombre de personnes) qui se met à jour sans recharger la page, et le détail de chaque menu (plats, allergènes, conditions, stock restant).

Côté utilisateur connecté : inscription, connexion, mot de passe oublié, commande d'un menu avec calcul du prix (frais de livraison au km si hors Bordeaux, -10% à partir de 5 personnes au-dessus du minimum), suivi et annulation de commande tant qu'elle n'est pas acceptée, et un avis à laisser une fois la commande terminée.

Côté employé : gestion des menus, plats et horaires, suivi des commandes avec changement de statut (acceptée, en préparation, en livraison, livrée, retour matériel, terminée), et validation/refus des avis clients.

Côté admin : les mêmes droits qu'un employé, plus la création/désactivation de comptes employés et des statistiques (nombre de commandes et CA par menu, filtrable par période) avec des graphiques Chart.js branchés sur MongoDB.

Il y a aussi un bandeau cookies (accepter/refuser, modifiable depuis le pied de page), les mentions légales et CGV, et un effort sur l'accessibilité (RGAA).

## Stack

- Front : HTML/CSS/JS vanilla, pas de framework front
- Back : PHP 8.2 en MVC fait maison (pas de framework type Symfony/Laravel)
- BDD relationnelle : MySQL (via PDO)
- BDD NoSQL : MongoDB, utilisée uniquement pour les stats admin
- Serveur : Apache
- Déploiement : Docker sur fly.io

## Installer en local (XAMPP)

Prérequis : PHP 8.2, MySQL/MariaDB, Composer, Apache avec mod_rewrite, et MongoDB + son extension PHP si on veut tester les stats admin.

1. Cloner le repo

```bash
git clone https://github.com/lena-dbs/vite-gourmand.git
cd vite-gourmand
```

2. Installer les dépendances

```bash
composer install
```

3. Créer la base et importer le schéma

```bash
mysql -u root -p < database/database.sql
```

(ou via phpMyAdmin : créer une base `vite_gourmand` puis importer `database/database.sql`)

4. Copier `.env.example` en `.env` et renseigner ses propres valeurs (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, MONGO_URI, APP_URL). Le fichier `.env` n'est pas versionné (voir `.gitignore`), donc chacun garde ses propres identifiants en local.

5. Pointer le DocumentRoot Apache vers `public/`. Sous XAMPP, dans `httpd-vhosts.conf` :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/vite-gourmand/public"
    <Directory "C:/xampp/htdocs/vite-gourmand/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Vérifier que `mod_rewrite` est activé dans `httpd.conf`.

6. Démarrer Apache + MySQL depuis XAMPP, puis aller sur `http://localhost`.

### Comptes de test

Le script SQL crée trois comptes de démonstration, actifs, pour tester les trois parcours. Le mot de passe est le même pour les trois : `password` (stocké haché en bcrypt, jamais en clair).

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | jose@vitegourmand.fr | password |
| Employé | julie@vitegourmand.fr | password |
| Utilisateur (client) | test@email.fr | password |

Ces identifiants sont aussi rappelés dans le manuel d'utilisation (livrable PDF). En contexte réel, ces comptes de démonstration seraient retirés ou désactivés.

## Déployer sur fly.io

```bash
fly auth login
fly launch
fly deploy
```

Les secrets (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, MONGO_URI, APP_URL) se configurent dans les secrets fly.io, jamais dans le dépôt.

L'app tourne dans un conteneur Docker (PHP 8.2 + Apache).

Si un déploiement ajoute une table (ex: `login_attempt`), il faut lancer la migration :

```bash
fly ssh console -a vite-gourmand-old-lagoon-1903 -C "php /var/www/html/database/migrate.php"
```

`migrate.php` crée aussi (de façon idempotente) le backfill des statistiques dans MongoDB à partir des commandes MySQL. Les statistiques admin (nombre de commandes et CA par menu) sont lues depuis MongoDB ; il faut donc lancer cette migration au moins une fois après avoir importé `database.sql`, en local comme en production, pour que le graphique affiche les données de démonstration.

`deploy.ps1` enchaîne tout ça automatiquement (lint PHP, commit, push, deploy, migration, quelques vérifs après déploiement).

## Structure

```
vite-gourmand/
├── app/
│   ├── Controllers/    Admin, Auth, Commande, Contact, Employe, Home, Legal, Menu, User
│   ├── Models/         Commande, Horaire, Menu, User
│   └── Views/          templates PHP rangés par section (admin, auth, commande, contact,
│                       employe, errors, home, layouts, legal, menus, user)
├── config/
│   └── config.php      config BDD et constantes
├── core/
│   ├── autoload.php
│   ├── Controller.php  contrôleur de base (render, CSRF, redirect)
│   ├── Database.php    singleton PDO
│   ├── Model.php
│   ├── MongoStats.php  client MongoDB pour les stats
│   └── Router.php
├── database/
│   ├── database.sql    schéma + données de démo
│   └── migrate.php     migrations à lancer en CLI
├── docs/                livrables PDF (manuel d'utilisation, documentation technique,
│                       charte graphique, gestion de projet)
├── public/
│   ├── index.php       point d'entrée unique
│   ├── .htaccess
│   └── assets/         css, js, images
├── .env                 pas versionné
├── composer.json
├── deploy.ps1
├── Dockerfile
├── fly.toml
└── README.md
```

## Sécurité

Quelques points que j'ai essayé de couvrir :

- CSRF sur tous les formulaires en POST
- mots de passe en bcrypt
- requêtes préparées PDO partout, pas de concaténation SQL
- échappement systématique en sortie (`htmlspecialchars`) contre le XSS
- headers de sécurité (CSP, X-Frame-Options, HSTS...)
- sessions en httponly / samesite strict, expiration à 30 min
- limitation du nombre de tentatives de connexion (table `login_attempt`)
- rien de sensible dans le dépôt : les identifiants de base de données et l'URI MongoDB de prod sont dans les secrets fly.io, les valeurs locales dans un `.env` ignoré par git. Seuls les comptes de démonstration (mot de passe `password`) sont volontairement fournis pour l'évaluation ; ils seraient retirés en contexte réel

## Licence

Projet réalisé dans le cadre du TP Développeur Web et Web Mobile, Studi / FastDev.
