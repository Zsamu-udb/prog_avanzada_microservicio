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
        $sql = "INSERT INTO clientes (nombre, apellido, documento, telefono, email, licencia_conducir)
                VALUES (:nombre, :apellido, :documento, :telefono, :email, :licencia_conducir)";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':nombre', $cliente->getNombre());
        $stmt->bindValue(':apellido', $cliente->getApellido());
        $stmt->bindValue(':documento', $cliente->getDocumento());
        $stmt->bindValue(':telefono', $cliente->getTelefono());
        $stmt->bindValue(':email', $cliente->getEmail());
        $stmt->bindValue(':licencia_conducir', $cliente->getLicenciaConducir());
        $stmt->execute();

        $cliente->setId((int) $this->getConnection()->lastInsertId());

        return $cliente;
    }

    public function update(Cliente $cliente): bool
    {
        $sql = "UPDATE clientes
                SET nombre = :nombre,
                    apellido = :apellido,
                    documento = :documento,
                    telefono = :telefono,
                    email = :email,
                    licencia_conducir = :licencia_conducir
                WHERE id = :id";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $cliente->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $cliente->getNombre());
        $stmt->bindValue(':apellido', $cliente->getApellido());
        $stmt->bindValue(':documento', $cliente->getDocumento());
        $stmt->bindValue(':telefono', $cliente->getTelefono());
        $stmt->bindValue(':email', $cliente->getEmail());
        $stmt->bindValue(':licencia_conducir', $cliente->getLicenciaConducir());

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
            $row['apellido'],
            $row['documento'],
            $row['telefono'] ?? null,
            $row['email'] ?? null,
            $row['licencia_conducir'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}