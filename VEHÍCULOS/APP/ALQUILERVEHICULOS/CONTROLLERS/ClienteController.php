<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

use App\AlquilerVehiculos\Models\Cliente;
use App\AlquilerVehiculos\Presentation\Repositories\ClienteRepository;
use InvalidArgumentException;
use Throwable;

class ClienteController
{
    private ClienteRepository $repository;

    public function __construct()
    {
        $this->repository = new ClienteRepository();
    }

    public function index(): void
    {
        $clientes = $this->repository->findAll();
        $data = array_map(fn(Cliente $cliente) => $cliente->toArray(), $clientes);

        $this->jsonResponse($data, 200);
    }

    public function show(int $id): void
    {
        $cliente = $this->repository->findById($id);

        if (!$cliente) {
            $this->jsonResponse(['message' => 'Cliente no encontrado'], 404);
            return;
        }

        $this->jsonResponse($cliente->toArray(), 200);
    }

    public function store(): void
    {
        try {
            $data = $this->getJsonInput();

            $cliente = Cliente::create(
                $data['nombre'] ?? '',
                $data['apellido'] ?? '',
                $data['documento'] ?? '',
                $data['telefono'] ?? null,
                $data['email'] ?? null,
                $data['licencia_conducir'] ?? ''
            );

            $clienteCreado = $this->repository->create($cliente);
            $this->jsonResponse($clienteCreado->toArray(), 201);
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al crear el cliente'], 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $cliente = $this->repository->findById($id);

            if (!$cliente) {
                $this->jsonResponse(['message' => 'Cliente no encontrado'], 404);
                return;
            }

            $data = $this->getJsonInput();

            $cliente->updateData(
                $data['nombre'] ?? $cliente->getNombre(),
                $data['apellido'] ?? $cliente->getApellido(),
                $data['documento'] ?? $cliente->getDocumento(),
                $data['telefono'] ?? $cliente->getTelefono(),
                $data['email'] ?? $cliente->getEmail(),
                $data['licencia_conducir'] ?? $cliente->getLicenciaConducir()
            );

            $this->repository->update($cliente);
            $this->jsonResponse($cliente->toArray(), 200);
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al actualizar el cliente'], 500);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $cliente = $this->repository->findById($id);

            if (!$cliente) {
                $this->jsonResponse(['message' => 'Cliente no encontrado'], 404);
                return;
            }

            $this->repository->delete($id);
            $this->jsonResponse(['message' => 'Cliente eliminado correctamente'], 200);
        } catch (Throwable $e) {
            $this->jsonResponse(['message' => 'Error al eliminar el cliente'], 500);
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