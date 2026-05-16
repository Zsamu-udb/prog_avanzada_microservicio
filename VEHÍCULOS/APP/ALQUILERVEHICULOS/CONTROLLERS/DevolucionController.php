<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

use App\AlquilerVehiculos\Models\Devolucion;
use App\AlquilerVehiculos\Presentation\Repositories\DevolucionRepository;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class DevolucionController extends BaseController
{
    private DevolucionRepository $repository;

    public function __construct()
    {
        $this->repository = new DevolucionRepository();
    }

    public function index(): void
    {
        $devoluciones = $this->repository->findAll();
        $data = array_map(
            fn(Devolucion $devolucion) => $devolucion->toArray(),
            $devoluciones
        );

        $this->successResponse($data);
    }

    public function show(int $id): void
    {
        $devolucion = $this->repository->findById($id);

        if (!$devolucion instanceof Devolucion) {
            $this->errorResponse('Devolución no encontrada.', 404);
            return;
        }

        $this->successResponse($devolucion->toArray());
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonInput();

            $devolucion = Devolucion::create(
                (int) ($data['reserva_id'] ?? 0),
                $data['fecha_devolucion'] ?? '',
                $data['observaciones'] ?? null,
                $data['estado_vehiculo'] ?? 'disponible'
            );

            $devolucionCreada = $this->repository->create($devolucion);

            $this->successResponse($devolucionCreada->toArray(), 201);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al registrar la devolución.', 500);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $devolucion = $this->repository->findById($id);

            if (!$devolucion instanceof Devolucion) {
                $this->errorResponse('Devolución no encontrada.', 404);
                return;
            }

            $this->repository->delete($id);

            $this->successResponse([
                'message' => 'Devolución eliminada correctamente.'
            ]);
        } catch (Throwable $e) {
            $this->errorResponse('Error al eliminar la devolución.', 500);
        }
    }
}