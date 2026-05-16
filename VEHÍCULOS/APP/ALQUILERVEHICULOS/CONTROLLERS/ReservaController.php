<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

use App\AlquilerVehiculos\Models\Reserva;
use App\AlquilerVehiculos\Presentation\Repositories\ReservaRepository;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ReservaController extends BaseController
{
    private ReservaRepository $repository;

    public function __construct()
    {
        $this->repository = new ReservaRepository();
    }

    public function index(): void
    {
        $reservas = $this->repository->findAll();
        $data = array_map(
            fn(Reserva $reserva) => $reserva->toArray(),
            $reservas
        );

        $this->successResponse($data);
    }

    public function show(int $id): void
    {
        $reserva = $this->repository->findById($id);

        if (!$reserva instanceof Reserva) {
            $this->errorResponse('Reserva no encontrada.', 404);
            return;
        }

        $this->successResponse($reserva->toArray());
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonInput();

            $reserva = Reserva::create(
                (int) ($data['cliente_id'] ?? 0),
                (int) ($data['vehiculo_id'] ?? 0),
                $data['fecha_inicio'] ?? '',
                $data['fecha_fin'] ?? ''
            );

            $reservaCreada = $this->repository->create($reserva);

            $this->successResponse($reservaCreada->toArray(), 201);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al crear la reserva.', 500);
        }
    }

    public function finalizar(int $id): void
    {
        try {
            $reserva = $this->repository->findById($id);

            if (!$reserva instanceof Reserva) {
                $this->errorResponse('Reserva no encontrada.', 404);
                return;
            }

            $this->repository->finalizar($id);

            $this->successResponse([
                'message' => 'Reserva finalizada correctamente.'
            ]);
        } catch (Throwable $e) {
            $this->errorResponse('Error al finalizar la reserva.', 500);
        }
    }

    public function cancelar(int $id): void
    {
        try {
            $reserva = $this->repository->findById($id);

            if (!$reserva instanceof Reserva) {
                $this->errorResponse('Reserva no encontrada.', 404);
                return;
            }

            $this->repository->cancelar($id);

            $this->successResponse([
                'message' => 'Reserva cancelada correctamente.'
            ]);
        } catch (RuntimeException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al cancelar la reserva.', 500);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $reserva = $this->repository->findById($id);

            if (!$reserva instanceof Reserva) {
                $this->errorResponse('Reserva no encontrada.', 404);
                return;
            }

            $this->repository->delete($id);

            $this->successResponse([
                'message' => 'Reserva eliminada correctamente.'
            ]);
        } catch (Throwable $e) {
            $this->errorResponse('Error al eliminar la reserva.', 500);
        }
    }
}