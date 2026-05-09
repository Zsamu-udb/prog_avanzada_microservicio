<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private string $host = 'localhost';
    private string $dbName = 'alquiler_vehiculos_db';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';

    public function connect(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";

            $pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            return $pdo;
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
}