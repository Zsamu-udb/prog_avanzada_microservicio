<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Presentation\Repositories;

use App\Config\Database;
use App\AlquilerVehiculos\Models\Vehiculo;
use PDO;

class VehiculoRepository
{
    private PDO $connection;

    public function __construct()
    {
        $database = new Database();
        $this->connection = $database->connect();
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM vehiculos ORDER BY id DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $vehiculos = [];
        while ($row = $stmt->fetch()) {
            $vehiculos[] = $this->mapRowToVehiculo($row);
        }

        return $vehiculos;
    }

    public function findById(int $id): ?Vehiculo
    {
        $sql = "SELECT * FROM vehiculos WHERE id = :id LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ? $this->mapRowToVehiculo($row) : null;
    }

    public function create(Vehiculo $vehiculo): Vehiculo
    {
        $sql = "INSERT INTO vehiculos (placa, marca, modelo, anio, categoria, estado, precio_por_dia)
                VALUES (:placa, :marca, :modelo, :anio, :categoria, :estado, :precio_por_dia)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':placa', $vehiculo->getPlaca());
        $stmt->bindValue(':marca', $vehiculo->getMarca());
        $stmt->bindValue(':modelo', $vehiculo->getModelo());
        $stmt->bindValue(':anio', $vehiculo->getAnio());
        $stmt->bindValue(':categoria', $vehiculo->getCategoria());
        $stmt->bindValue(':estado', $vehiculo->getEstado());
        $stmt->bindValue(':precio_por_dia', $vehiculo->getPrecioPorDia());
        $stmt->execute();

        $vehiculo->setId((int) $this->connection->lastInsertId());
        return $vehiculo;
    }

    public function update(Vehiculo $vehiculo): bool
    {
        $sql = "UPDATE vehiculos
                SET placa = :placa,
                    marca = :marca,
                    modelo = :modelo,
                    anio = :anio,
                    categoria = :categoria,
                    estado = :estado,
                    precio_por_dia = :precio_por_dia
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $vehiculo->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':placa', $vehiculo->getPlaca());
        $stmt->bindValue(':marca', $vehiculo->getMarca());
        $stmt->bindValue(':modelo', $vehiculo->getModelo());
        $stmt->bindValue(':anio', $vehiculo->getAnio());
        $stmt->bindValue(':categoria', $vehiculo->getCategoria());
        $stmt->bindValue(':estado', $vehiculo->getEstado());
        $stmt->bindValue(':precio_por_dia', $vehiculo->getPrecioPorDia());

        return $stmt->execute();
    }

    public function updateEstado(int $id, string $estado): bool
    {
        $sql = "UPDATE vehiculos SET estado = :estado WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':estado', $estado);

        return $stmt->execute();
    }

    public function findDisponibles(): array
    {
        $sql = "SELECT * FROM vehiculos WHERE estado = 'disponible' ORDER BY id DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $vehiculos = [];
        while ($row = $stmt->fetch()) {
            $vehiculos[] = $this->mapRowToVehiculo($row);
        }

        return $vehiculos;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM vehiculos WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function mapRowToVehiculo(array $row): Vehiculo
    {
        return new Vehiculo(
            isset($row['id']) ? (int) $row['id'] : null,
            $row['placa'],
            $row['marca'],
            $row['modelo'],
            (string) $row['anio'],
            $row['categoria'],
            $row['estado'],
            (float) $row['precio_por_dia'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}