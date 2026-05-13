<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private string $host = 'localhost';
    private string $port = '3306';
    private string $dbName = 'alquiler_vehiculos_db';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';

    public function connect(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset={$this->charset}";

            return new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
}