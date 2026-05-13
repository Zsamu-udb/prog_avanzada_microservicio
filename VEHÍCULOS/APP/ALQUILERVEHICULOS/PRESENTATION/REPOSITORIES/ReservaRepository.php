<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Presentation\Repositories;

use App\Config\Database;
use App\AlquilerVehiculos\Models\Reserva;
use PDO;
use RuntimeException;
use Throwable;

class ReservaRepository
{
    private PDO $connection;

    public function __construct()
    {
        $database = new Database();
        $this->connection = $database->connect();
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM reservas ORDER BY id DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $reservas = [];
        while ($row = $stmt->fetch()) {
            $reservas[] = $this->mapRowToReserva($row);
        }

        return $reservas;
    }

    public function findById(int $id): ?Reserva
    {
        $sql = "SELECT * FROM reservas WHERE id = :id LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ? $this->mapRowToReserva($row) : null;
    }

    public function create(Reserva $reserva): Reserva
    {
        try {
            $this->connection->beginTransaction();

            if (!$this->clienteExiste($reserva->getClienteId())) {
                throw new RuntimeException('El cliente no existe.');
            }

            $vehiculo = $this->obtenerVehiculo($reserva->getVehiculoId());
            if (!$vehiculo) {
                throw new RuntimeException('El vehículo no existe.');
            }

            if ($vehiculo['estado'] !== 'disponible') {
                throw new RuntimeException('El vehículo no está disponible para reserva.');
            }

            if ($this->existeCruceDeReserva(
                $reserva->getVehiculoId(),
                $reserva->getFechaInicio(),
                $reserva->getFechaFin()
            )) {
                throw new RuntimeException('Ya existe una reserva activa para ese vehículo en ese rango de fechas.');
            }

            $precioPorDia = (float) $vehiculo['precio_por_dia'];
            $reserva->recalcularTotal($precioPorDia);

            $sql = "INSERT INTO reservas (cliente_id, vehiculo_id, fecha_inicio, fecha_fin, estado, total_estimado)
                    VALUES (:cliente_id, :vehiculo_id, :fecha_inicio, :fecha_fin, :estado, :total_estimado)";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':cliente_id', $reserva->getClienteId(), PDO::PARAM_INT);
            $stmt->bindValue(':vehiculo_id', $reserva->getVehiculoId(), PDO::PARAM_INT);
            $stmt->bindValue(':fecha_inicio', $reserva->getFechaInicio());
            $stmt->bindValue(':fecha_fin', $reserva->getFechaFin());
            $stmt->bindValue(':estado', $reserva->getEstado());
            $stmt->bindValue(':total_estimado', $reserva->getTotalEstimado());
            $stmt->execute();

            $reserva->setId((int) $this->connection->lastInsertId());

            $sqlVehiculo = "UPDATE vehiculos SET estado = 'alquilado' WHERE id = :id";
            $stmtVehiculo = $this->connection->prepare($sqlVehiculo);
            $stmtVehiculo->bindValue(':id', $reserva->getVehiculoId(), PDO::PARAM_INT);
            $stmtVehiculo->execute();

            $this->connection->commit();
            return $reserva;
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
        }
    }

    public function finalizar(int $id): bool
    {
        $sql = "UPDATE reservas SET estado = 'finalizada' WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function cancelar(int $id): bool
    {
        try {
            $this->connection->beginTransaction();

            $reserva = $this->findById($id);
            if (!$reserva) {
                throw new RuntimeException('La reserva no existe.');
            }

            $sqlReserva = "UPDATE reservas SET estado = 'cancelada' WHERE id = :id";
            $stmtReserva = $this->connection->prepare($sqlReserva);
            $stmtReserva->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtReserva->execute();

            $sqlVehiculo = "UPDATE vehiculos SET estado = 'disponible' WHERE id = :vehiculo_id";
            $stmtVehiculo = $this->connection->prepare($sqlVehiculo);
            $stmtVehiculo->bindValue(':vehiculo_id', $reserva->getVehiculoId(), PDO::PARAM_INT);
            $stmtVehiculo->execute();

            $this->connection->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
        }
    }

    private function clienteExiste(int $clienteId): bool
    {
        $sql = "SELECT COUNT(*) FROM clientes WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    private function obtenerVehiculo(int $vehiculoId): ?array
    {
        $sql = "SELECT * FROM vehiculos WHERE id = :id LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $vehiculoId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function existeCruceDeReserva(int $vehiculoId, string $fechaInicio, string $fechaFin): bool
    {
        $sql = "SELECT COUNT(*) 
                FROM reservas
                WHERE vehiculo_id = :vehiculo_id
                  AND estado = 'activa'
                  AND NOT (:fecha_fin < fecha_inicio OR :fecha_inicio > fecha_fin)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':vehiculo_id', $vehiculoId, PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $fechaInicio);
        $stmt->bindValue(':fecha_fin', $fechaFin);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    private function mapRowToReserva(array $row): Reserva
    {
        return new Reserva(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['cliente_id'],
            (int) $row['vehiculo_id'],
            $row['fecha_inicio'],
            $row['fecha_fin'],
            $row['estado'],
            (float) $row['total_estimado'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}