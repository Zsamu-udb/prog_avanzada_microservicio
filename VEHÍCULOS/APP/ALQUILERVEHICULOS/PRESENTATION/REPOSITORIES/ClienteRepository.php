<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Presentation\Repositories;

use ALQUILERVEHICULOS\Controllers\ClienteController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ClienteRepository extends AbstractRepository
{
    public function all(Request $request, Response $response): Response
    {
        $controller = new ClienteController();
        $clientes = $controller->getClientes();

        return $this->json($response, $clientes);
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $this->obtenerDatos($request);

            $controller = new ClienteController();
            $cliente = $controller->guardarCliente($data);

            return $this->json($response, $cliente, 201);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function detail(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new ClienteController();
            $cliente = $controller->getCliente($id);

            return $this->json($response, $cliente->toJson());
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $this->obtenerDatos($request);

            $controller = new ClienteController();
            $cliente = $controller->modificarCliente($id, $data);

            return $this->json($response, $cliente->toJson(), 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new ClienteController();
            $controller->borrarCliente($id);

            return $this->json($response, [
                'message' => 'Cliente borrado correctamente'
            ], 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }
    public function historial(Request $request, Response $response, array $args): Response
{
    try {

        $id = (int)$args['id'];

        $controller = new ClienteController();

        $cliente = $controller->getHistorialReservas($id);

        return $this->json(
            $response,
            $cliente->toJson()
        );

    } catch (Exception $exception) {
        return $this->jsonError($response, $exception);
    }
}
}