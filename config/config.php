<?php
declare(strict_types=1);

// MongoDB
define('MONGO_URI', getenv('MONGO_URI') ?: 'mongodb+srv://lenadbsts:SECRET_RETIRE@cluster0.byfq2r6.mongodb.net/vite_gourmand?appName=Cluster0');

// Base de données
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'vite_gourmand');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'Laulau21001?');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// Application
define('APP_NAME', 'Vite & Gourmand');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_VERSION', '1.0.0');

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/Views');
define('ASSETS_PATH', '/assets');

