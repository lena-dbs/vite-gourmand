<?php
declare(strict_types=1);
// Test isolé de la connexion MongoDB (à lancer en CLI) :
//   C:\xampp\php\php.exe database\test_mongo.php
// Diagnostique séparément la connexion / l'authentification Mongo, sans toucher à MySQL.

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
require_once __DIR__ . '/../config/config.php';

echo "URI (hôte) : ";
echo preg_replace('#(mongodb(\+srv)?://)[^@]*@#', '$1***:***@', MONGO_URI) . "\n";

try {
    $client = new MongoDB\Client(MONGO_URI, ['serverSelectionTimeoutMS' => 5000]);
    $client->selectDatabase('vite_gourmand')->command(['ping' => 1]);
    echo "Mongo OK : connexion + authentification reussies.\n";
    $n = $client->vite_gourmand->statistiques->countDocuments([]);
    echo "Documents dans la collection 'statistiques' : $n\n";
} catch (\Throwable $e) {
    echo "Mongo ECHEC : " . $e->getMessage() . "\n";
}
