<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Models;

use DateTime;
use InvalidArgumentException;

class Reserva extends AbstractModel
{
    private const ESTADOS_VALIDOS = ['activa', 'finalizada', 'cancelada'];

    private int $clienteId;
    private int $vehiculoId;
    private string $fechaInicio;
    private string $fechaFin;
    private string $estado;
    private float $totalEstimado;

    public function __construct(
        ?int $id,
        int $clienteId,
        int $vehiculoId,
        string $fechaInicio,
        string $fechaFin,
        string $estado = 'activa',
        float $totalEstimado = 0.0,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($id, $createdAt, $updatedAt);

        $this->setClienteId($clienteId);
        $this->setVehiculoId($vehiculoId);
        $this->setFechaInicio($fechaInicio);
        $this->setFechaFin($fechaFin);
        $this->setEstado($estado);
        $this->setTotalEstimado($totalEstimado);
        $this->validateDateRange();
    }

    public static function create(
        int $clienteId,
        int $vehiculoId,
        string $fechaInicio,
        string $fechaFin
    ): self {
        return new self(
            null,
            $clienteId,
            $vehiculoId,
            $fechaInicio,
            $fechaFin
        );
    }

    public function updatePeriodo(string $fechaInicio, string $fechaFin): void
    {
        $this->setFechaInicio($fechaInicio);
        $this->setFechaFin($fechaFin);
        $this->validateDateRange();
    }

    public function finalizar(): void
    {
        $this->estado = 'finalizada';
    }

    public function cancelar(): void
    {
        $this->estado = 'cancelada';
    }

    public function recalcularTotal(float $precioPorDia): void
    {
        if ($precioPorDia < 0) {
            throw new InvalidArgumentException('El precio por día no puede ser negativo.');
        }

        $inicio = new DateTime($this->fechaInicio);
        $fin = new DateTime($this->fechaFin);
        $dias = (int) $inicio->diff($fin)->days;

        if ($dias <= 0) {
            $dias = 1;
        }

        $this->totalEstimado = $dias * $precioPorDia;
    }

    public function getClienteId(): int
    {
        return $this->clienteId;
    }

    public function getVehiculoId(): int
    {
        return $this->vehiculoId;
    }

    public function getFechaInicio(): string
    {
        return $this->fechaInicio;
    }

    public function getFechaFin(): string
    {
        return $this->fechaFin;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getTotalEstimado(): float
    {
        return $this->totalEstimado;
    }

    private function setClienteId(int $clienteId): void
    {
        if ($clienteId <= 0) {
            throw new InvalidArgumentException('El cliente_id debe ser mayor que cero.');
        }

        $this->clienteId = $clienteId;
    }

    private function setVehiculoId(int $vehiculoId): void
    {
        if ($vehiculoId <= 0) {
            throw new InvalidArgumentException('El vehiculo_id debe ser mayor que cero.');
        }

        $this->vehiculoId = $vehiculoId;
    }

    private function setFechaInicio(string $fechaInicio): void
    {
        $this->assertValidDate($fechaInicio, 'La fecha de inicio no es válida.');
        $this->fechaInicio = $fechaInicio;
    }

    private function setFechaFin(string $fechaFin): void
    {
        $this->assertValidDate($fechaFin, 'La fecha de fin no es válida.');
        $this->fechaFin = $fechaFin;
    }

    private function setEstado(string $estado): void
    {
        $estado = strtolower(trim($estado));

        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('El estado de la reserva no es válido.');
        }

        $this->estado = $estado;
    }

    private function setTotalEstimado(float $totalEstimado): void
    {
        if ($totalEstimado < 0) {
            throw new InvalidArgumentException('El total estimado no puede ser negativo.');
        }

        $this->totalEstimado = $totalEstimado;
    }

    private function validateDateRange(): void
    {
        if ($this->fechaFin < $this->fechaInicio) {
            throw new InvalidArgumentException('La fecha fin no puede ser menor que la fecha inicio.');
        }
    }

    private function assertValidDate(string $date, string $message): void
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);

        if (!$dateTime || $dateTime->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException($message);
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'cliente_id' => $this->clienteId,
            'vehiculo_id' => $this->vehiculoId,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'estado' => $this->estado,
            'total_estimado' => $this->totalEstimado,
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }
}