<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

use App\AlquilerVehiculos\Models\Cliente;
use App\AlquilerVehiculos\Presentation\Repositories\ClienteRepository;
use InvalidArgumentException;
use Throwable;

class ClienteController extends BaseController
{
    private ClienteRepository $repository;

    public function __construct()
    {
        $this->repository = new ClienteRepository();
    }

    public function index(): void
    {
        $clientes = $this->repository->findAll();
        $data = array_map(
            fn(Cliente $cliente) => $cliente->toArray(),
            $clientes
        );

        $this->successResponse($data);
    }

    public function show(int $id): void
    {
        $cliente = $this->repository->findById($id);

        if (!$cliente instanceof Cliente) {
            $this->errorResponse('Cliente no encontrado.', 404);
            return;
        }

        $this->successResponse($cliente->toArray());
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

            $this->successResponse($clienteCreado->toArray(), 201);
        } catch (InvalidArgumentException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al crear el cliente.', 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $cliente = $this->repository->findById($id);

            if (!$cliente instanceof Cliente) {
                $this->errorResponse('Cliente no encontrado.', 404);
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

            $this->successResponse($cliente->toArray());
        } catch (InvalidArgumentException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->errorResponse('Error al actualizar el cliente.', 500);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $cliente = $this->repository->findById($id);

            if (!$cliente instanceof Cliente) {
                $this->errorResponse('Cliente no encontrado.', 404);
                return;
            }

            $this->repository->delete($id);

            $this->successResponse([
                'message' => 'Cliente eliminado correctamente.'
            ]);
        } catch (Throwable $e) {
            $this->errorResponse('Error al eliminar el cliente.', 500);
        }
    }
}