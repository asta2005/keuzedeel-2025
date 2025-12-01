<?php
namespace App;

class Database {
    private $pdo;
    public function __construct(string $path) {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        $this->pdo = new \PDO('sqlite:' . $path);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->init();
    }

    private function init() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            municipality TEXT NOT NULL,
            description TEXT,
            image TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");
    }

    public function getPdo() {
        return $this->pdo;
    }
}
