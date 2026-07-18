<?php
declare(strict_types=1);

// Migration idempotente — à lancer en CLI uniquement :
//   fly ssh console -C "php /var/www/html/database/migrate.php"
if (php_sapi_name() !== 'cli') {
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

require_once __DIR__ . '/../config/config.php';

$db = new PDO(
    'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$db->exec('
    CREATE TABLE IF NOT EXISTS `login_attempt` (
        `attempt_id`      INT          NOT NULL AUTO_INCREMENT,
        `email`           VARCHAR(255) NOT NULL,
        `ip`              VARCHAR(45)  NOT NULL,
        `type`            VARCHAR(20)  NOT NULL DEFAULT \'login\',
        `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`attempt_id`),
        INDEX `idx_attempt_email` (`email`, `created_at`),
        INDEX `idx_attempt_ip` (`ip`, `created_at`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4
');

echo "Migration OK : table login_attempt en place.\n";
