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
        $vehiculos = $this->repository->findAvailable();
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
                $data['categoria'] ?? '',
                $data['placa'] ?? '',
                (float) ($data['precio_por_dia'] ?? 0),
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
            $vehiculo = $this->repository->findById($id);

            if (!$vehiculo instanceof Vehiculo) {
                $this->errorResponse('Vehículo no encontrado.', 404);
                return;
            }

            $data = $this->getJsonInput();

            $vehiculo->updateData(
                $data['marca'] ?? $vehiculo->getMarca(),
                $data['modelo'] ?? $vehiculo->getModelo(),
                (int) ($data['anio'] ?? $vehiculo->getAnio()),
                $data['categoria'] ?? $vehiculo->getCategoria(),
                $data['placa'] ?? $vehiculo->getPlaca(),
                (float) ($data['precio_por_dia'] ?? $vehiculo->getPrecioPorDia()),
                $data['estado'] ?? $vehiculo->getEstado()
            );

            $this->repository->update($vehiculo);

            $this->successResponse($vehiculo->toArray());
        } catch (InvalidArgumentException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al actualizar el vehículo.', 500);
        }
    }

    public function updateEstado(int $id): void
    {
        try {
            $vehiculo = $this->repository->findById($id);

            if (!$vehiculo instanceof Vehiculo) {
                $this->errorResponse('Vehículo no encontrado.', 404);
                return;
            }

            $data = $this->getJsonInput();
            $estado = $data['estado'] ?? '';

            $vehiculo->updateData(
                $vehiculo->getMarca(),
                $vehiculo->getModelo(),
                $vehiculo->getAnio(),
                $vehiculo->getCategoria(),
                $vehiculo->getPlaca(),
                $vehiculo->getPrecioPorDia(),
                $estado
            );

            $this->repository->updateEstado($id, $vehiculo->getEstado());

            $this->successResponse($vehiculo->toArray());
        } catch (InvalidArgumentException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al cambiar el estado del vehículo.', 500);
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