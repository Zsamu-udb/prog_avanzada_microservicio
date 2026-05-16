<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Models;

use InvalidArgumentException;

class Cliente extends AbstractModel
{
    private string $nombre;
    private string $apellido;
    private string $documento;
    private ?string $telefono;
    private ?string $email;
    private string $licenciaConducir;

    public function __construct(
        ?int $id,
        string $nombre,
        string $apellido,
        string $documento,
        ?string $telefono,
        ?string $email,
        string $licenciaConducir,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($id, $createdAt, $updatedAt);

        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setDocumento($documento);
        $this->setTelefono($telefono);
        $this->setEmail($email);
        $this->setLicenciaConducir($licenciaConducir);
    }

    public static function create(
        string $nombre,
        string $apellido,
        string $documento,
        ?string $telefono,
        ?string $email,
        string $licenciaConducir
    ): self {
        return new self(
            null,
            $nombre,
            $apellido,
            $documento,
            $telefono,
            $email,
            $licenciaConducir
        );
    }

    public function updateData(
        string $nombre,
        string $apellido,
        string $documento,
        ?string $telefono,
        ?string $email,
        string $licenciaConducir
    ): void {
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setDocumento($documento);
        $this->setTelefono($telefono);
        $this->setEmail($email);
        $this->setLicenciaConducir($licenciaConducir);
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getDocumento(): string
    {
        return $this->documento;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getLicenciaConducir(): string
    {
        return $this->licenciaConducir;
    }

    public function getNombreCompleto(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    private function setNombre(string $nombre): void
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre no puede estar vacío.');
        }

        $this->nombre = $nombre;
    }

    private function setApellido(string $apellido): void
    {
        $apellido = trim($apellido);
        if ($apellido === '') {
            throw new InvalidArgumentException('El apellido no puede estar vacío.');
        }

        $this->apellido = $apellido;
    }

    private function setDocumento(string $documento): void
    {
        $documento = trim($documento);
        if ($documento === '') {
            throw new InvalidArgumentException('El documento no puede estar vacío.');
        }

        $this->documento = $documento;
    }

    private function setTelefono(?string $telefono): void
    {
        $this->telefono = $telefono !== null ? trim($telefono) : null;
    }

    private function setEmail(?string $email): void
    {
        if ($email !== null && trim($email) !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El correo electrónico no es válido.');
        }

        $this->email = $email !== null ? trim($email) : null;
    }

    private function setLicenciaConducir(string $licenciaConducir): void
    {
        $licenciaConducir = trim($licenciaConducir);
        if ($licenciaConducir === '') {
            throw new InvalidArgumentException('La licencia de conducir no puede estar vacía.');
        }

        $this->licenciaConducir = $licenciaConducir;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'nombre_completo' => $this->getNombreCompleto(),
            'documento' => $this->documento,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'licencia_conducir' => $this->licenciaConducir,
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }
}