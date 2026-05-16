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
    private ?string $categoria;
    private string $estado;

    public function __construct(
        ?int $id,
        string $marca,
        string $modelo,
        int $anio,
        ?string $categoria = null,
        string $estado = 'disponible',
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($id, $createdAt, $updatedAt);

        $this->setMarca($marca);
        $this->setModelo($modelo);
        $this->setAnio($anio);
        $this->setCategoria($categoria);
        $this->setEstado($estado);
    }

    public static function create(
        string $marca,
        string $modelo,
        int $anio,
        ?string $categoria = null,
        string $estado = 'disponible'
    ): self {
        return new self(
            null,
            $marca,
            $modelo,
            $anio,
            $categoria,
            $estado
        );
    }

    public function updateData(
        string $marca,
        string $modelo,
        int $anio,
        ?string $categoria = null,
        string $estado = 'disponible'
    ): void {
        $this->setMarca($marca);
        $this->setModelo($modelo);
        $this->setAnio($anio);
        $this->setCategoria($categoria);
        $this->setEstado($estado);
    }

    public function cambiarEstado(string $estado): void
    {
        $this->setEstado($estado);
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

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    private function setMarca(string $marca): void
    {
        $marca = trim($marca);

        if ($marca === '') {
            throw new InvalidArgumentException('La marca del vehículo es obligatoria.');
        }

        $this->marca = $marca;
    }

    private function setModelo(string $modelo): void
    {
        $modelo = trim($modelo);

        if ($modelo === '') {
            throw new InvalidArgumentException('El modelo del vehículo es obligatorio.');
        }

        $this->modelo = $modelo;
    }

    private function setAnio(int $anio): void
    {
        $currentYear = (int) date('Y') + 1;

        if ($anio < 1900 || $anio > $currentYear) {
            throw new InvalidArgumentException('El año del vehículo no es válido.');
        }

        $this->anio = $anio;
    }

    private function setCategoria(?string $categoria): void
    {
        $categoria = $categoria !== null ? trim($categoria) : null;
        $this->categoria = $categoria !== '' ? $categoria : null;
    }

    private function setEstado(string $estado): void
    {
        $estado = strtolower(trim($estado));

        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('El estado del vehículo no es válido.');
        }

        $this->estado = $estado;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'anio' => $this->anio,
            'categoria' => $this->categoria,
            'estado' => $this->estado,
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }
}