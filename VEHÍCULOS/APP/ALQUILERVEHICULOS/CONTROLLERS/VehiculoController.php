<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

use App\AlquilerVehiculos\Models\Vehiculo;
use App\AlquilerVehiculos\Presentation\Repositories\VehiculoRepository;
use InvalidArgumentException;
use Throwable;

class VehiculoController
{
    private VehiculoRepository $repository;

    public function __construct()
    {
        $this->repository = new VehiculoRepository();
    }

    public function index(): void
    {
        $vehiculos = $this->repository->findAll();
        $data = array_map(fn(Vehiculo $vehiculo) => $vehiculo->toArray(), $vehiculos);

        $this->jsonResponse($data, 200);
    }

    public function disponibles(): void
    {
        $vehiculos = $this->repository->findDisponibles();
        $data = array_map(fn(Vehiculo $vehiculo) => $vehiculo->toArray(), $vehiculos);

        $this->jsonResponse($data, 200);
    }

    public function show(int $id): void
    {
        $vehiculo = $this->repository->findById($id);

        if (!$vehiculo) {
            $this->jsonResponse(['message' => 'Vehículo no encontrado'], 404);
            return;
        }

        $this->jsonResponse($vehiculo->toArray(), 200);
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonInput();

            $vehiculo = Vehiculo::create(
                $data['placa'] ?? '',
                $data['marca'] ?? '',
                $data['modelo'] ?? '',
                (string) ($data['anio'] ?? ''),
                $data['categoria'] ?? '',
                isset($data['precio_por_dia']) ? (float) $data['precio_por_dia'] : 0.00
            );

            $vehiculoCreado = $this->repository->create($vehiculo);
            $this->jsonResponse($vehiculoCreado->toArray(), 201);
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al crear el vehículo'], 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $vehiculo = $this->repository->findById($id);

            if (!$vehiculo) {
                $this->jsonResponse(['message' => 'Vehículo no encontrado'], 404);
                return;
            }

            $data = $this->getJsonInput();

            $vehiculo->updateData(
                $data['placa'] ?? $vehiculo->getPlaca(),
                $data['marca'] ?? $vehiculo->getMarca(),
                $data['modelo'] ?? $vehiculo->getModelo(),
                (string) ($data['anio'] ?? $vehiculo->getAnio()),
                $data['categoria'] ?? $vehiculo->getCategoria(),
                isset($data['precio_por_dia']) ? (float) $data['precio_por_dia'] : $vehiculo->getPrecioPorDia()
            );

            if (isset($data['estado'])) {
                $vehiculo->setEstado($data['estado']);
            }

            $this->repository->update($vehiculo);
            $this->jsonResponse($vehiculo->toArray(), 200);
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al actualizar el vehículo'], 500);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $vehiculo = $this->repository->findById($id);

            if (!$vehiculo) {
                $this->jsonResponse(['message' => 'Vehículo no encontrado'], 404);
                return;
            }

            $this->repository->delete($id);
            $this->jsonResponse(['message' => 'Vehículo eliminado correctamente'], 200);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al eliminar el vehículo'], 500);
        }
    }

    public function changeEstado(int $id): void
    {
        try {
            $vehiculo = $this->repository->findById($id);

            if (!$vehiculo) {
                $this->jsonResponse(['message' => 'Vehículo no encontrado'], 404);
                return;
            }

            $data = $this->getJsonInput();
            $estado = $data['estado'] ?? '';

            $vehiculo->setEstado($estado);
            $this->repository->updateEstado($id, $vehiculo->getEstado());

            $this->jsonResponse($vehiculo->toArray(), 200);
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al cambiar el estado del vehículo'], 500);
        }
    }

    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return is_array($data) ? $data : [];
    }

    private function jsonResponse(array $data, int $statusCode): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}