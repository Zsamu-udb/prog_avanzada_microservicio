<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Presentation\Repositories;

use ALQUILERVEHICULOS\Controllers\VehiculoController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VehiculoRepository extends AbstractRepository
{
    public function all(Request $request, Response $response): Response
    {
        $controller = new VehiculoController();
        $vehiculos = $controller->getVehiculos();

        return $this->json($response, $vehiculos);
    }

    public function disponibles(Request $request, Response $response): Response
    {
        $controller = new VehiculoController();
        $vehiculos = $controller->getVehiculosDisponibles();

        return $this->json($response, $vehiculos);
    }

    public function detail(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new VehiculoController();
            $vehiculo = $controller->getVehiculo($id);

            return $this->json($response, $vehiculo->toJson());
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $this->obtenerDatos($request);

            $controller = new VehiculoController();
            $vehiculo = $controller->guardarVehiculo($data);

            return $this->json($response, $vehiculo, 201);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $this->obtenerDatos($request);

            $controller = new VehiculoController();
            $vehiculo = $controller->modificarVehiculo($id, $data);

            return $this->json($response, $vehiculo->toJson(), 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function changeEstado(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $this->obtenerDatos($request);

            $controller = new VehiculoController();
            $vehiculo = $controller->cambiarEstadoVehiculo($id, $data);

            return $this->json($response, $vehiculo->toJson(), 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new VehiculoController();
            $controller->borrarVehiculo($id);

            return $this->json($response, [
                'message' => 'Vehículo borrado correctamente'
            ], 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }
}