<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

use App\AlquilerVehiculos\Models\Reserva;
use App\AlquilerVehiculos\Presentation\Repositories\ReservaRepository;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ReservaController
{
    private ReservaRepository $repository;

    public function __construct()
    {
        $this->repository = new ReservaRepository();
    }

    public function index(): void
    {
        $reservas = $this->repository->findAll();
        $data = array_map(fn(Reserva $reserva) => $reserva->toArray(), $reservas);

        $this->jsonResponse($data, 200);
    }

    public function show(int $id): void
    {
        $reserva = $this->repository->findById($id);

        if (!$reserva) {
            $this->jsonResponse(['message' => 'Reserva no encontrada'], 404);
            return;
        }

        $this->jsonResponse($reserva->toArray(), 200);
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
            $this->jsonResponse($reservaCreada->toArray(), 201);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al crear la reserva'], 500);
        }
    }

    public function finalizar(int $id): void
    {
        try {
            $reserva = $this->repository->findById($id);

            if (!$reserva) {
                $this->jsonResponse(['message' => 'Reserva no encontrada'], 404);
                return;
            }

            $this->repository->finalizar($id);
            $this->jsonResponse(['message' => 'Reserva finalizada correctamente'], 200);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al finalizar la reserva'], 500);
        }
    }

    public function cancelar(int $id): void
    {
        try {
            $reserva = $this->repository->findById($id);

            if (!$reserva) {
                $this->jsonResponse(['message' => 'Reserva no encontrada'], 404);
                return;
            }

            $this->repository->cancelar($id);
            $this->jsonResponse(['message' => 'Reserva cancelada correctamente'], 200);
        } catch (RuntimeException $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al cancelar la reserva'], 500);
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