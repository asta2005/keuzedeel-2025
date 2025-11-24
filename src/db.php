<?php
namespace App;

class DB {
    private static $pdo = null;

    public static function get() {
        if (self::$pdo) return self::$pdo;
        $host = getenv('DB_HOST') ?: 'mariadb';
        $db   = getenv('DB_NAME') ?: 'omarkahouach';
        $user = getenv('DB_USERNAME') ?: 'omar';
        $pass = getenv('DB_PASSWORD') ?: 'khaled';
        $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
        try {
            self::$pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            return self::$pdo;
        } catch (\PDOException $e) {
            http_response_code(500);
            echo 'DB connection failed: ' . htmlspecialchars($e->getMessage());
            exit;
        }
    }
}
