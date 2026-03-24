<?php

class Database {
    private static $instance = null;
    private $conn;

    private $host = 'localhost';
    private $user = 'root';
    private $pass = ''; 
    private $name = 'db_reservasi_hotel';

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
                PDO::ATTR_EMULATE_PREPARES   => false,                  
            ];
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}
