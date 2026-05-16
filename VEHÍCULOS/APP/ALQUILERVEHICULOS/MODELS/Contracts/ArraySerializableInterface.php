<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Models\Contracts;

interface ArraySerializableInterface
{
    public function toArray(): array;
}