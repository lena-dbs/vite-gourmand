<?php 
declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        
        // Test si la connexion est encore active
        try {
            self::$instance->query('SELECT 1');
        } catch (PDOException $e) {
            self::$instance = null;
            self::connect();
        }

        return self::$instance;
    }

    private static function connect(): void
    {
        try {
            self::$instance = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES         => false,
                    PDO::ATTR_PERSISTENT               => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND       => "SET NAMES 'utf8mb4'",
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                ]
            );
        } catch(PDOException $e) {
            die('Erreur de connexion : ' . $e->getMessage());
        }
    }

    private function __construct() {}
    private function __clone() {}
}