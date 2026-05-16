<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Presentation\Repositories;

use App\AlquilerVehiculos\Models\Devolucion;
use App\AlquilerVehiculos\Presentation\Repositories\Contracts\RepositoryInterface;
use PDO;
use RuntimeException;
use Throwable;

class DevolucionRepository extends BaseRepository implements RepositoryInterface
{
    public function findAll(): array
    {
        $sql = "SELECT * FROM devoluciones ORDER BY id DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $devoluciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $devoluciones[] = $this->mapRowToDevolucion($row);
        }

        return $devoluciones;
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM devoluciones WHERE id = :id LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToDevolucion($row) : null;
    }

    public function create(Devolucion $devolucion): Devolucion
    {
        try {
            $this->getConnection()->beginTransaction();

            $reserva = $this->obtenerReserva($devolucion->getReservaId());
            if (!$reserva) {
                throw new RuntimeException('La reserva no existe.');
            }

            if ($this->reservaYaDevuelta($devolucion->getReservaId())) {
                throw new RuntimeException('La reserva ya tiene una devolución registrada.');
            }

            if ($reserva['estado'] === 'cancelada') {
                throw new RuntimeException('No se puede registrar devolución para una reserva cancelada.');
            }

            $sql = "INSERT INTO devoluciones (reserva_id, fecha_devolucion, observaciones, estado_vehiculo)
                    VALUES (:reserva_id, :fecha_devolucion, :observaciones, :estado_vehiculo)";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':reserva_id', $devolucion->getReservaId(), PDO::PARAM_INT);
            $stmt->bindValue(':fecha_devolucion', $devolucion->getFechaDevolucion());
            $stmt->bindValue(':observaciones', $devolucion->getObservaciones());
            $stmt->bindValue(':estado_vehiculo', $devolucion->getEstadoVehiculo());
            $stmt->execute();

            $devolucion->setId((int) $this->getConnection()->lastInsertId());

            $sqlReserva = "UPDATE reservas SET estado = 'finalizada' WHERE id = :id";
            $stmtReserva = $this->getConnection()->prepare($sqlReserva);
            $stmtReserva->bindValue(':id', $devolucion->getReservaId(), PDO::PARAM_INT);
            $stmtReserva->execute();

            $sqlVehiculo = "UPDATE vehiculos
                            SET estado = :estado_vehiculo
                            WHERE id = :vehiculo_id";

            $stmtVehiculo = $this->getConnection()->prepare($sqlVehiculo);
            $stmtVehiculo->bindValue(':estado_vehiculo', $devolucion->getEstadoVehiculo());
            $stmtVehiculo->bindValue(':vehiculo_id', (int) $reserva['vehiculo_id'], PDO::PARAM_INT);
            $stmtVehiculo->execute();

            $this->getConnection()->commit();

            return $devolucion;
        } catch (Throwable $e) {
            if ($this->getConnection()->inTransaction()) {
                $this->getConnection()->rollBack();
            }

            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM devoluciones WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function obtenerReserva(int $reservaId): ?array
    {
        $sql = "SELECT * FROM reservas WHERE id = :id LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $reservaId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function reservaYaDevuelta(int $reservaId): bool
    {
        $sql = "SELECT COUNT(*) FROM devoluciones WHERE reserva_id = :reserva_id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':reserva_id', $reservaId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    private function mapRowToDevolucion(array $row): Devolucion
    {
        return new Devolucion(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['reserva_id'],
            $row['fecha_devolucion'],
            $row['observaciones'] ?? null,
            $row['estado_vehiculo'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}