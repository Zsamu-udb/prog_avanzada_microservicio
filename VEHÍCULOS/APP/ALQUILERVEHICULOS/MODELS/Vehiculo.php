<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Models;

use InvalidArgumentException;

class Vehiculo extends AbstractModel
{
    private const ESTADOS_VALIDOS = ['disponible', 'alquilado', 'mantenimiento'];

    private string $marca;
    private string $modelo;
    private int $anio;
    private string $categoria;
    private string $placa;
    private string $estado;
    private float $precioPorDia;

    public function __construct(
        ?int $id,
        string $marca,
        string $modelo,
        int $anio,
        string $categoria,
        string $placa,
        string $estado,
        float $precioPorDia,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($id, $createdAt, $updatedAt);

        $this->setMarca($marca);
        $this->setModelo($modelo);
        $this->setAnio($anio);
        $this->setCategoria($categoria);
        $this->setPlaca($placa);
        $this->setEstado($estado);
        $this->setPrecioPorDia($precioPorDia);
    }

    public static function create(
        string $marca,
        string $modelo,
        int $anio,
        string $categoria,
        string $placa,
        float $precioPorDia,
        string $estado = 'disponible'
    ): self {
        return new self(
            null,
            $marca,
            $modelo,
            $anio,
            $categoria,
            $placa,
            $estado,
            $precioPorDia
        );
    }

    public function updateData(
        string $marca,
        string $modelo,
        int $anio,
        string $categoria,
        string $placa,
        float $precioPorDia,
        string $estado
    ): void {
        $this->setMarca($marca);
        $this->setModelo($modelo);
        $this->setAnio($anio);
        $this->setCategoria($categoria);
        $this->setPlaca($placa);
        $this->setPrecioPorDia($precioPorDia);
        $this->setEstado($estado);
    }

    public function marcarDisponible(): void
    {
        $this->estado = 'disponible';
    }

    public function marcarAlquilado(): void
    {
        $this->estado = 'alquilado';
    }

    public function marcarEnMantenimiento(): void
    {
        $this->estado = 'mantenimiento';
    }

    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }

    public function getMarca(): string
    {
        return $this->marca;
    }

    public function getModelo(): string
    {
        return $this->modelo;
    }

    public function getAnio(): int
    {
        return $this->anio;
    }

    public function getCategoria(): string
    {
        return $this->categoria;
    }

    public function getPlaca(): string
    {
        return $this->placa;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getPrecioPorDia(): float
    {
        return $this->precioPorDia;
    }

    public function getNombreCompleto(): string
    {
        return $this->marca . ' ' . $this->modelo;
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

    private function setAnio(int $anio): void
    {
        $anioActual = (int) date('Y');
        if ($anio < 1900 || $anio > $anioActual + 1) {
            throw new InvalidArgumentException('El año del vehículo no es válido.');
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

    private function setPlaca(string $placa): void
    {
        $placa = strtoupper(trim($placa));
        if ($placa === '') {
            throw new InvalidArgumentException('La placa no puede estar vacía.');
        }

        $this->placa = $placa;
    }

    private function setEstado(string $estado): void
    {
        $estado = trim(strtolower($estado));

        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('El estado del vehículo no es válido.');
        }

        $this->estado = $estado;
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
            'id' => $this->getId(),
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'nombre_completo' => $this->getNombreCompleto(),
            'anio' => $this->anio,
            'categoria' => $this->categoria,
            'placa' => $this->placa,
            'estado' => $this->estado,
            'precio_por_dia' => $this->precioPorDia,
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }
}