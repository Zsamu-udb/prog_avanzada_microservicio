<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Presentation\Repositories;

use ALQUILERVEHICULOS\Controllers\ReservaController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservaRepository extends AbstractRepository
{
    public function all(Request $request, Response $response): Response
    {
        $controller = new ReservaController();
        $reservas = $controller->getReservas();

        return $this->json($response, $reservas);
    }

    public function detail(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new ReservaController();
            $reserva = $controller->getReserva($id);

            return $this->json($response, $reserva->toJson());
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $this->obtenerDatos($request);

            $controller = new ReservaController();
            $reserva = $controller->guardarReserva($data);

            return $this->json($response, $reserva, 201);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $this->obtenerDatos($request);

            $controller = new ReservaController();
            $reserva = $controller->modificarReserva($id, $data);

            return $this->json($response, $reserva->toJson(), 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function completar(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new ReservaController();
            $reserva = $controller->completarReserva($id);

            return $this->json($response, $reserva->toJson(), 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function cancelar(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new ReservaController();
            $reserva = $controller->cancelarReserva($id);

            return $this->json($response, $reserva->toJson(), 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new ReservaController();
            $controller->borrarReserva($id);

            return $this->json($response, [
                'message' => 'Reserva borrada correctamente'
            ], 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }
}