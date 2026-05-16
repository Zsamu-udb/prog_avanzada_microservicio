<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Presentation\Repositories;

use App\AlquilerVehiculos\Models\Cliente;
use App\AlquilerVehiculos\Presentation\Repositories\Contracts\RepositoryInterface;
use PDO;

class ClienteRepository extends BaseRepository implements RepositoryInterface
{
    public function findAll(): array
    {
        $sql = "SELECT * FROM clientes ORDER BY id DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $clientes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clientes[] = $this->mapRowToCliente($row);
        }

        return $clientes;
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM clientes WHERE id = :id LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToCliente($row) : null;
    }

    public function create(Cliente $cliente): Cliente
    {
        $sql = "INSERT INTO clientes (nombre, telefono, correo, numero_licencia)
                VALUES (:nombre, :telefono, :correo, :numero_licencia)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':nombre', $cliente->getNombre());
        $stmt->bindValue(':telefono', $cliente->getTelefono());
        $stmt->bindValue(':correo', $cliente->getCorreo());
        $stmt->bindValue(':numero_licencia', $cliente->getNumeroLicencia());
        $stmt->execute();

        $cliente->setId((int) $this->getConnection()->lastInsertId());

        return $cliente;
    }

    public function update(int $id, Cliente $cliente): bool
    {
        $sql = "UPDATE clientes
                SET nombre = :nombre,
                    telefono = :telefono,
                    correo = :correo,
                    numero_licencia = :numero_licencia
                WHERE id = :id";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $cliente->getNombre());
        $stmt->bindValue(':telefono', $cliente->getTelefono());
        $stmt->bindValue(':correo', $cliente->getCorreo());
        $stmt->bindValue(':numero_licencia', $cliente->getNumeroLicencia());

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM clientes WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function mapRowToCliente(array $row): Cliente
    {
        return new Cliente(
            isset($row['id']) ? (int) $row['id'] : null,
            $row['nombre'],
            $row['telefono'] ?? null,
            $row['correo'] ?? null,
            $row['numero_licencia'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}