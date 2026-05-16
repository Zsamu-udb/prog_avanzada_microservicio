<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Models;

use App\AlquilerVehiculos\Models\Contracts\ArraySerializableInterface;

abstract class AbstractModel implements ArraySerializableInterface
{
    protected ?int $id;
    protected ?string $createdAt;
    protected ?string $updatedAt;

    public function __construct(
        ?int $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    abstract public function toArray(): array;
}