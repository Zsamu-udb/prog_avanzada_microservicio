<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Models;

use DateTime;
use InvalidArgumentException;

class Devolucion
{
    private const ESTADOS_VALIDOS = ['disponible', 'mantenimiento'];

    private ?int $id;
    private int $reservaId;
    private string $fechaDevolucion;
    private ?string $observaciones;
    private string $estadoVehiculo;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        int $reservaId,
        string $fechaDevolucion,
        ?string $observaciones = null,
        string $estadoVehiculo = 'disponible',
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->setReservaId($reservaId);
        $this->setFechaDevolucion($fechaDevolucion);
        $this->setObservaciones($observaciones);
        $this->setEstadoVehiculo($estadoVehiculo);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        int $reservaId,
        string $fechaDevolucion,
        ?string $observaciones = null,
        string $estadoVehiculo = 'disponible'
    ): self {
        return new self(
            null,
            $reservaId,
            $fechaDevolucion,
            $observaciones,
            $estadoVehiculo
        );
    }

    public function marcarVehiculoDisponible(): void
    {
        $this->estadoVehiculo = 'disponible';
    }

    public function marcarVehiculoEnMantenimiento(): void
    {
        $this->estadoVehiculo = 'mantenimiento';
    }

    public function getId(): ?int { return $this->id; }
    public function getReservaId(): int { return $this->reservaId; }
    public function getFechaDevolucion(): string { return $this->fechaDevolucion; }
    public function getObservaciones(): ?string { return $this->observaciones; }
    public function getEstadoVehiculo(): string { return $this->estadoVehiculo; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    private function setReservaId(int $reservaId): void
    {
        if ($reservaId <= 0) {
            throw new InvalidArgumentException('El reserva_id debe ser mayor que cero.');
        }
        $this->reservaId = $reservaId;
    }

    private function setFechaDevolucion(string $fechaDevolucion): void
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $fechaDevolucion);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $fechaDevolucion) {
            throw new InvalidArgumentException('La fecha de devolución no es válida.');
        }
        $this->fechaDevolucion = $fechaDevolucion;
    }

    private function setObservaciones(?string $observaciones): void
    {
        $this->observaciones = $observaciones !== null ? trim($observaciones) : null;
    }

    public function setEstadoVehiculo(string $estadoVehiculo): void
    {
        if (!in_array($estadoVehiculo, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('El estado del vehículo no es válido para devolución.');
        }
        $this->estadoVehiculo = $estadoVehiculo;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reserva_id' => $this->reservaId,
            'fecha_devolucion' => $this->fechaDevolucion,
            'observaciones' => $this->observaciones,
            'estado_vehiculo' => $this->estadoVehiculo,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}