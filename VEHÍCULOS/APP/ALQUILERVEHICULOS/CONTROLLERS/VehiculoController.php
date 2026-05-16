<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

use App\AlquilerVehiculos\Models\Vehiculo;
use App\AlquilerVehiculos\Presentation\Repositories\VehiculoRepository;
use InvalidArgumentException;
use Throwable;

class VehiculoController extends BaseController
{
    private VehiculoRepository $repository;

    public function __construct()
    {
        $this->repository = new VehiculoRepository();
    }

    public function index(): void
    {
        $vehiculos = $this->repository->findAll();
        $data = array_map(
            fn(Vehiculo $vehiculo) => $vehiculo->toArray(),
            $vehiculos
        );

        $this->successResponse($data);
    }

    public function disponibles(): void
    {
        $vehiculos = $this->repository->findDisponibles();
        $data = array_map(
            fn(Vehiculo $vehiculo) => $vehiculo->toArray(),
            $vehiculos
        );

        $this->successResponse($data);
    }

    public function show(int $id): void
    {
        $vehiculo = $this->repository->findById($id);

        if (!$vehiculo instanceof Vehiculo) {
            $this->errorResponse('Vehículo no encontrado.', 404);
            return;
        }

        $this->successResponse($vehiculo->toArray());
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonInput();

            $vehiculo = Vehiculo::create(
                $data['marca'] ?? '',
                $data['modelo'] ?? '',
                (int) ($data['anio'] ?? 0),
                $data['categoria'] ?? null,
                $data['estado'] ?? 'disponible'
            );

            $vehiculoCreado = $this->repository->create($vehiculo);

            $this->successResponse($vehiculoCreado->toArray(), 201);
        } catch (InvalidArgumentException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al crear el vehículo.', 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $vehiculoActual = $this->repository->findById($id);

            if (!$vehiculoActual instanceof Vehiculo) {
                $this->errorResponse('Vehículo no encontrado.', 404);
                return;
            }

            $data = $this->getJsonInput();

            $vehiculoActual->updateData(
                $data['marca'] ?? '',
                $data['modelo'] ?? '',
                (int) ($data['anio'] ?? 0),
                $data['categoria'] ?? null,
                $data['estado'] ?? 'disponible'
            );

            $this->repository->update($id, $vehiculoActual);

            $this->successResponse([
                'message' => 'Vehículo actualizado correctamente.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al actualizar el vehículo.', 500);
        }
    }

    public function updateEstado(int $id): void
    {
        try {
            $vehiculoActual = $this->repository->findById($id);

            if (!$vehiculoActual instanceof Vehiculo) {
                $this->errorResponse('Vehículo no encontrado.', 404);
                return;
            }

            $data = $this->getJsonInput();
            $vehiculoActual->cambiarEstado($data['estado'] ?? '');

            $this->repository->updateEstado($id, $vehiculoActual->getEstado());

            $this->successResponse([
                'message' => 'Estado del vehículo actualizado correctamente.'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al actualizar el estado del vehículo.', 500);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $vehiculo = $this->repository->findById($id);

            if (!$vehiculo instanceof Vehiculo) {
                $this->errorResponse('Vehículo no encontrado.', 404);
                return;
            }

            $this->repository->delete($id);

            $this->successResponse([
                'message' => 'Vehículo eliminado correctamente.'
            ]);
        } catch (Throwable $e) {
            $this->errorResponse('Error al eliminar el vehículo.', 500);
        }
    }
}