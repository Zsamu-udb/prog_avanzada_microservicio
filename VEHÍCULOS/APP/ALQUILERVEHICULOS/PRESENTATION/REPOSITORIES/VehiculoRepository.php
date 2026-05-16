<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Presentation\Repositories;

use App\AlquilerVehiculos\Models\Vehiculo;
use App\AlquilerVehiculos\Presentation\Repositories\Contracts\RepositoryInterface;
use PDO;

class VehiculoRepository extends BaseRepository implements RepositoryInterface
{
    public function findAll(): array
    {
        $sql = "SELECT * FROM vehiculos ORDER BY id DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $vehiculos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vehiculos[] = $this->mapRowToVehiculo($row);
        }

        return $vehiculos;
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM vehiculos WHERE id = :id LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToVehiculo($row) : null;
    }

    public function findAvailable(): array
    {
        $sql = "SELECT * FROM vehiculos WHERE estado = 'disponible' ORDER BY id DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $vehiculos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vehiculos[] = $this->mapRowToVehiculo($row);
        }

        return $vehiculos;
    }

    public function create(Vehiculo $vehiculo): Vehiculo
    {
        $sql = "INSERT INTO vehiculos (marca, modelo, anio, categoria, placa, estado, precio_por_dia)
                VALUES (:marca, :modelo, :anio, :categoria, :placa, :estado, :precio_por_dia)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':marca', $vehiculo->getMarca());
        $stmt->bindValue(':modelo', $vehiculo->getModelo());
        $stmt->bindValue(':anio', $vehiculo->getAnio(), PDO::PARAM_INT);
        $stmt->bindValue(':categoria', $vehiculo->getCategoria());
        $stmt->bindValue(':placa', $vehiculo->getPlaca());
        $stmt->bindValue(':estado', $vehiculo->getEstado());
        $stmt->bindValue(':precio_por_dia', $vehiculo->getPrecioPorDia());
        $stmt->execute();

        $vehiculo->setId((int) $this->getConnection()->lastInsertId());

        return $vehiculo;
    }

    public function update(Vehiculo $vehiculo): bool
    {
        $sql = "UPDATE vehiculos
                SET marca = :marca,
                    modelo = :modelo,
                    anio = :anio,
                    categoria = :categoria,
                    placa = :placa,
                    estado = :estado,
                    precio_por_dia = :precio_por_dia
                WHERE id = :id";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $vehiculo->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':marca', $vehiculo->getMarca());
        $stmt->bindValue(':modelo', $vehiculo->getModelo());
        $stmt->bindValue(':anio', $vehiculo->getAnio(), PDO::PARAM_INT);
        $stmt->bindValue(':categoria', $vehiculo->getCategoria());
        $stmt->bindValue(':placa', $vehiculo->getPlaca());
        $stmt->bindValue(':estado', $vehiculo->getEstado());
        $stmt->bindValue(':precio_por_dia', $vehiculo->getPrecioPorDia());

        return $stmt->execute();
    }

    public function updateEstado(int $id, string $estado): bool
    {
        $sql = "UPDATE vehiculos SET estado = :estado WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM vehiculos WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function mapRowToVehiculo(array $row): Vehiculo
    {
        return new Vehiculo(
            isset($row['id']) ? (int) $row['id'] : null,
            $row['marca'],
            $row['modelo'],
            (int) $row['anio'],
            $row['categoria'],
            $row['placa'],
            $row['estado'],
            (float) $row['precio_por_dia'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}