<?php
declare(strict_types=1);

class MongoStats
{
    private static ?\MongoDB\Client $instance = null;

    public static function getInstance(): \MongoDB\Client
    {
        if (self::$instance === null) {
            self::$instance = new MongoDB\Client(MONGO_URI, [
                'serverSelectionTimeoutMS' => 2000,
                'connectTimeoutMS'         => 2000,
                'socketTimeoutMS'          => 3000,
            ]);
        }
        return self::$instance;
    }

    public static function getCollection(string $collection): \MongoDB\Collection
    {
        return self::getInstance()->vite_gourmand->$collection;
    }
}