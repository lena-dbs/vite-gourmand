<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/autoload.php';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

define('CSP_NONCE', base64_encode(random_bytes(16)));

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-" . CSP_NONCE . "' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https://images.unsplash.com https://plus.unsplash.com https://images.pexels.com; frame-src https://www.google.com; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

$sessionOptions = [
    'cookie_httponly'  => true,
    'cookie_secure'    => $isHttps,
    'cookie_samesite'  => 'Strict',
    'use_strict_mode'  => true,
    'gc_maxlifetime'   => 1800,
    'cookie_lifetime'  => 0,
];

session_start($sessionOptions);

if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    session_start($sessionOptions);
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/../core/Router.php';

$router = new Router();

// Routes publiques
$router->add('GET',  '/',           'HomeController',  'index');
$router->add('GET',  '/menus',      'MenuController',  'index');
$router->add('GET',  '/menus/:id',  'MenuController',  'detail');

// Auth
$router->add('GET',  '/connexion',   'AuthController', 'login');
$router->add('POST', '/connexion',   'AuthController', 'login');
$router->add('GET',  '/inscription', 'AuthController', 'register');
$router->add('POST', '/inscription', 'AuthController', 'register');
$router->add('POST', '/deconnexion', 'AuthController', 'logout');

// Commande
$router->add('GET',  '/commande', 'CommandeController', 'index');
$router->add('POST', '/commande', 'CommandeController', 'store');

// Espace utilisateur
$router->add('GET',  '/mon-compte',               'UserController', 'index');
$router->add('GET',  '/mon-compte/commandes/:id', 'UserController', 'commande');
$router->add('POST', '/mon-compte/annuler',       'UserController', 'cancelCommande');
$router->add('GET',  '/mon-compte/profil',        'UserController', 'profil');
$router->add('POST', '/mon-compte/profil',        'UserController', 'profil');

// Espace employé
$router->add('GET',  '/employe',              'EmployeController', 'index');
$router->add('GET',  '/employe/commande',     'EmployeController', 'commande');
$router->add('POST', '/employe/statut',       'EmployeController', 'updateStatut');
$router->add('GET',  '/employe/menus',        'EmployeController', 'menus');
$router->add('POST', '/employe/menus/toggle', 'EmployeController', 'toggleMenu');
$router->add('GET',  '/employe/avis',         'EmployeController', 'avis');
$router->add('POST', '/employe/avis/update',  'EmployeController', 'updateAvis');

// Espace admin
$router->add('GET',  '/admin',                  'AdminController', 'index');
$router->add('GET',  '/admin/commande',         'AdminController', 'commande');
$router->add('POST', '/admin/statut',           'AdminController', 'updateStatut');
$router->add('GET',  '/admin/menus',            'AdminController', 'menus');
$router->add('POST', '/admin/menus/toggle',     'AdminController', 'toggleMenu');
$router->add('POST', '/admin/menus/stock',      'AdminController', 'updateStock');
$router->add('GET',  '/admin/avis',             'AdminController', 'avis');
$router->add('POST', '/admin/avis/update',      'AdminController', 'updateAvis');
$router->add('GET',  '/admin/employes',         'AdminController', 'employes');
$router->add('GET',  '/admin/employes/create',  'AdminController', 'createEmploye');
$router->add('POST', '/admin/employes/create',  'AdminController', 'createEmploye');
$router->add('POST', '/admin/employes/toggle',  'AdminController', 'toggleEmploye');
$router->add('GET',  '/admin/stats',            'AdminController', 'stats');


// Contact
$router->add('GET',  '/contact', 'ContactController', 'index');
$router->add('POST', '/contact', 'ContactController', 'index');

// Légal
$router->add('GET', '/mentions-legales', 'LegalController', 'mentions');
$router->add('GET', '/cgv',              'LegalController', 'cgv');

// Mot de passe oublié
$router->add('GET',  '/mot-de-passe-oublie',          'AuthController', 'forgotPassword');
$router->add('POST', '/mot-de-passe-oublie',          'AuthController', 'forgotPassword');
$router->add('GET',  '/reinitialiser-mot-de-passe',   'AuthController', 'resetPassword');
$router->add('POST', '/reinitialiser-mot-de-passe',   'AuthController', 'resetPassword');

$router->dispatch();