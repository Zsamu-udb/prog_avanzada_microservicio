<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Models;

use InvalidArgumentException;

class Cliente extends AbstractModel
{
    private string $nombre;
    private ?string $telefono;
    private ?string $correo;
    private ?string $numeroLicencia;

    public function __construct(
        ?int $id,
        string $nombre,
        ?string $telefono = null,
        ?string $correo = null,
        ?string $numeroLicencia = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($id, $createdAt, $updatedAt);

        $this->setNombre($nombre);
        $this->setTelefono($telefono);
        $this->setCorreo($correo);
        $this->setNumeroLicencia($numeroLicencia);
    }

    public static function create(
        string $nombre,
        ?string $telefono = null,
        ?string $correo = null,
        ?string $numeroLicencia = null
    ): self {
        return new self(
            null,
            $nombre,
            $telefono,
            $correo,
            $numeroLicencia
        );
    }

    public function updateData(
        string $nombre,
        ?string $telefono = null,
        ?string $correo = null,
        ?string $numeroLicencia = null
    ): void {
        $this->setNombre($nombre);
        $this->setTelefono($telefono);
        $this->setCorreo($correo);
        $this->setNumeroLicencia($numeroLicencia);
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function getNumeroLicencia(): ?string
    {
        return $this->numeroLicencia;
    }

    private function setNombre(string $nombre): void
    {
        $nombre = trim($nombre);

        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del cliente es obligatorio.');
        }

        $this->nombre = $nombre;
    }

    private function setTelefono(?string $telefono): void
    {
        $telefono = $telefono !== null ? trim($telefono) : null;
        $this->telefono = $telefono !== '' ? $telefono : null;
    }

    private function setCorreo(?string $correo): void
    {
        $correo = $correo !== null ? trim($correo) : null;

        if ($correo === '') {
            $correo = null;
        }

        if ($correo !== null && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El correo del cliente no es válido.');
        }

        $this->correo = $correo;
    }

    private function setNumeroLicencia(?string $numeroLicencia): void
    {
        $numeroLicencia = $numeroLicencia !== null ? trim($numeroLicencia) : null;
        $this->numeroLicencia = $numeroLicencia !== '' ? $numeroLicencia : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'numero_licencia' => $this->numeroLicencia,
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }
}