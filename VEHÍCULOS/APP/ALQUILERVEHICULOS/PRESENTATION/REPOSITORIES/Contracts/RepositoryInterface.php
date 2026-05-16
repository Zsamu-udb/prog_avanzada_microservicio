<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Presentation\Repositories\Contracts;

interface RepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?object;

    public function delete(int $id): bool;
}