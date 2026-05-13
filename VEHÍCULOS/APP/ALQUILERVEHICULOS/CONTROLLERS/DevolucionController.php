<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

use App\AlquilerVehiculos\Models\Devolucion;
use App\AlquilerVehiculos\Presentation\Repositories\DevolucionRepository;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class DevolucionController
{
    private DevolucionRepository $repository;

    public function __construct()
    {
        $this->repository = new DevolucionRepository();
    }

    public function index(): void
    {
        $devoluciones = $this->repository->findAll();
        $data = array_map(fn(Devolucion $devolucion) => $devolucion->toArray(), $devoluciones);

        $this->jsonResponse($data, 200);
    }

    public function show(int $id): void
    {
        $devolucion = $this->repository->findById($id);

        if (!$devolucion) {
            $this->jsonResponse(['message' => 'Devolución no encontrada'], 404);
            return;
        }

        $this->jsonResponse($devolucion->toArray(), 200);
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
            $this->jsonResponse($devolucionCreada->toArray(), 201);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al registrar la devolución'], 500);
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