<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Presentation\Repositories;

use App\AlquilerVehiculos\Models\Reserva;
use App\AlquilerVehiculos\Presentation\Repositories\Contracts\RepositoryInterface;
use PDO;
use RuntimeException;
use Throwable;

class ReservaRepository extends BaseRepository implements RepositoryInterface
{
    public function findAll(): array
    {
        $sql = "SELECT * FROM reservas ORDER BY id DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $reservas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservas[] = $this->mapRowToReserva($row);
        }

        return $reservas;
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM reservas WHERE id = :id LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToReserva($row) : null;
    }

    public function create(Reserva $reserva): Reserva
    {
        try {
            $this->getConnection()->beginTransaction();

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

            $sql = "INSERT INTO reservas (cliente_id, vehiculo_id, fecha_inicio, fecha_fin, estado)
                    VALUES (:cliente_id, :vehiculo_id, :fecha_inicio, :fecha_fin, :estado)";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':cliente_id', $reserva->getClienteId(), PDO::PARAM_INT);
            $stmt->bindValue(':vehiculo_id', $reserva->getVehiculoId(), PDO::PARAM_INT);
            $stmt->bindValue(':fecha_inicio', $reserva->getFechaInicio());
            $stmt->bindValue(':fecha_fin', $reserva->getFechaFin());
            $stmt->bindValue(':estado', $reserva->getEstado());
            $stmt->execute();

            $reserva->setId((int) $this->getConnection()->lastInsertId());

            $sqlVehiculo = "UPDATE vehiculos SET estado = 'alquilado' WHERE id = :id";
            $stmtVehiculo = $this->getConnection()->prepare($sqlVehiculo);
            $stmtVehiculo->bindValue(':id', $reserva->getVehiculoId(), PDO::PARAM_INT);
            $stmtVehiculo->execute();

            $this->getConnection()->commit();

            return $reserva;
        } catch (Throwable $e) {
            if ($this->getConnection()->inTransaction()) {
                $this->getConnection()->rollBack();
            }

            throw $e;
        }
    }

    public function update(int $id, Reserva $reserva): bool
    {
        $sql = "UPDATE reservas
                SET cliente_id = :cliente_id,
                    vehiculo_id = :vehiculo_id,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    estado = :estado
                WHERE id = :id";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':cliente_id', $reserva->getClienteId(), PDO::PARAM_INT);
        $stmt->bindValue(':vehiculo_id', $reserva->getVehiculoId(), PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $reserva->getFechaInicio());
        $stmt->bindValue(':fecha_fin', $reserva->getFechaFin());
        $stmt->bindValue(':estado', $reserva->getEstado());

        return $stmt->execute();
    }

    public function completar(int $id): bool
    {
        try {
            $this->getConnection()->beginTransaction();

            $reserva = $this->findById($id);
            if (!$reserva instanceof Reserva) {
                throw new RuntimeException('La reserva no existe.');
            }

            $sqlReserva = "UPDATE reservas SET estado = 'completada' WHERE id = :id";
            $stmtReserva = $this->getConnection()->prepare($sqlReserva);
            $stmtReserva->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtReserva->execute();

            $sqlVehiculo = "UPDATE vehiculos SET estado = 'disponible' WHERE id = :vehiculo_id";
            $stmtVehiculo = $this->getConnection()->prepare($sqlVehiculo);
            $stmtVehiculo->bindValue(':vehiculo_id', $reserva->getVehiculoId(), PDO::PARAM_INT);
            $stmtVehiculo->execute();

            $this->getConnection()->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->getConnection()->inTransaction()) {
                $this->getConnection()->rollBack();
            }

            throw $e;
        }
    }

    public function cancelar(int $id): bool
    {
        try {
            $this->getConnection()->beginTransaction();

            $reserva = $this->findById($id);
            if (!$reserva instanceof Reserva) {
                throw new RuntimeException('La reserva no existe.');
            }

            $sqlReserva = "UPDATE reservas SET estado = 'cancelada' WHERE id = :id";
            $stmtReserva = $this->getConnection()->prepare($sqlReserva);
            $stmtReserva->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtReserva->execute();

            $sqlVehiculo = "UPDATE vehiculos SET estado = 'disponible' WHERE id = :vehiculo_id";
            $stmtVehiculo = $this->getConnection()->prepare($sqlVehiculo);
            $stmtVehiculo->bindValue(':vehiculo_id', $reserva->getVehiculoId(), PDO::PARAM_INT);
            $stmtVehiculo->execute();

            $this->getConnection()->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->getConnection()->inTransaction()) {
                $this->getConnection()->rollBack();
            }

            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM reservas WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function clienteExiste(int $clienteId): bool
    {
        $sql = "SELECT COUNT(*) FROM clientes WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $clienteId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    private function obtenerVehiculo(int $vehiculoId): ?array
    {
        $sql = "SELECT * FROM vehiculos WHERE id = :id LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $vehiculoId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function existeCruceDeReserva(int $vehiculoId, string $fechaInicio, string $fechaFin): bool
    {
        $sql = "SELECT COUNT(*)
                FROM reservas
                WHERE vehiculo_id = :vehiculo_id
                  AND estado = 'activa'
                  AND NOT (:fecha_fin < fecha_inicio OR :fecha_inicio > fecha_fin)";

        $stmt = $this->getConnection()->prepare($sql);
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
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}