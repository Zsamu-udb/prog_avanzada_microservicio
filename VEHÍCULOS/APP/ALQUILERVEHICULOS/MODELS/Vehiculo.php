<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Models;

use InvalidArgumentException;

class Vehiculo
{
    private const ESTADOS_VALIDOS = ['disponible', 'alquilado', 'mantenimiento'];

    private ?int $id;
    private string $placa;
    private string $marca;
    private string $modelo;
    private string $anio;
    private string $categoria;
    private string $estado;
    private float $precioPorDia;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        string $placa,
        string $marca,
        string $modelo,
        string $anio,
        string $categoria,
        string $estado = 'disponible',
        float $precioPorDia = 0.00,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->setPlaca($placa);
        $this->setMarca($marca);
        $this->setModelo($modelo);
        $this->setAnio($anio);
        $this->setCategoria($categoria);
        $this->setEstado($estado);
        $this->setPrecioPorDia($precioPorDia);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        string $placa,
        string $marca,
        string $modelo,
        string $anio,
        string $categoria,
        float $precioPorDia
    ): self {
        return new self(
            null,
            $placa,
            $marca,
            $modelo,
            $anio,
            $categoria,
            'disponible',
            $precioPorDia
        );
    }

    public function updateData(
        string $placa,
        string $marca,
        string $modelo,
        string $anio,
        string $categoria,
        float $precioPorDia
    ): void {
        $this->setPlaca($placa);
        $this->setMarca($marca);
        $this->setModelo($modelo);
        $this->setAnio($anio);
        $this->setCategoria($categoria);
        $this->setPrecioPorDia($precioPorDia);
    }

    public function marcarDisponible(): void
    {
        $this->estado = 'disponible';
    }

    public function marcarAlquilado(): void
    {
        $this->estado = 'alquilado';
    }

    public function marcarMantenimiento(): void
    {
        $this->estado = 'mantenimiento';
    }

    public function getId(): ?int { return $this->id; }
    public function getPlaca(): string { return $this->placa; }
    public function getMarca(): string { return $this->marca; }
    public function getModelo(): string { return $this->modelo; }
    public function getAnio(): string { return $this->anio; }
    public function getCategoria(): string { return $this->categoria; }
    public function getEstado(): string { return $this->estado; }
    public function getPrecioPorDia(): float { return $this->precioPorDia; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setEstado(string $estado): void
    {
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('El estado del vehículo no es válido.');
        }
        $this->estado = $estado;
    }

    private function setPlaca(string $placa): void
    {
        $placa = strtoupper(trim($placa));
        if ($placa === '') {
            throw new InvalidArgumentException('La placa no puede estar vacía.');
        }
        $this->placa = $placa;
    }

    private function setMarca(string $marca): void
    {
        $marca = trim($marca);
        if ($marca === '') {
            throw new InvalidArgumentException('La marca no puede estar vacía.');
        }
        $this->marca = $marca;
    }

    private function setModelo(string $modelo): void
    {
        $modelo = trim($modelo);
        if ($modelo === '') {
            throw new InvalidArgumentException('El modelo no puede estar vacío.');
        }
        $this->modelo = $modelo;
    }

    private function setAnio(string $anio): void
    {
        $anio = trim($anio);
        if (!preg_match('/^\d{4}$/', $anio)) {
            throw new InvalidArgumentException('El año debe tener 4 dígitos.');
        }
        $this->anio = $anio;
    }

    private function setCategoria(string $categoria): void
    {
        $categoria = trim($categoria);
        if ($categoria === '') {
            throw new InvalidArgumentException('La categoría no puede estar vacía.');
        }
        $this->categoria = $categoria;
    }

    private function setPrecioPorDia(float $precioPorDia): void
    {
        if ($precioPorDia < 0) {
            throw new InvalidArgumentException('El precio por día no puede ser negativo.');
        }
        $this->precioPorDia = $precioPorDia;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'placa' => $this->placa,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'anio' => $this->anio,
            'categoria' => $this->categoria,
            'estado' => $this->estado,
            'precio_por_dia' => $this->precioPorDia,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}