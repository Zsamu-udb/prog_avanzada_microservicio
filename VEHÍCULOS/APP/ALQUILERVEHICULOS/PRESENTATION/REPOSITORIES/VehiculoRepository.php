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

    public function uploadImagen(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $controller = new VehiculoController();
            $controller->getVehiculo($id);

            $uploadedFiles = $request->getUploadedFiles();
            $imagen = $uploadedFiles['imagen'] ?? null;

            if ($imagen === null) {
                throw new Exception('Debes enviar el archivo en el campo "imagen".');
            }

            if ($imagen->getError() !== UPLOAD_ERR_OK) {
                throw new Exception('No fue posible subir la imagen.');
            }

            $clientFilename = $imagen->getClientFilename() ?? '';
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));

            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $extensionesPermitidas, true)) {
                throw new Exception('Formato no permitido. Solo se aceptan jpg, jpeg, png o webp.');
            }

            $publicPath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'public';
            $uploadDir = $publicPath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'vehiculos';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach (glob($uploadDir . DIRECTORY_SEPARATOR . $id . '.*') as $archivoExistente) {
                if (is_file($archivoExistente)) {
                    unlink($archivoExistente);
                }
            }

            $filename = $id . '.' . $extension;
            $filepath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

            $imagen->moveTo($filepath);

            return $this->json($response, [
                'message' => 'Imagen subida correctamente',
                'vehiculo_id' => $id,
                'imagen_url' => '/uploads/vehiculos/' . $filename
            ], 200);
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

            $publicPath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'public';
            $uploadDir = $publicPath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'vehiculos';

            foreach (glob($uploadDir . DIRECTORY_SEPARATOR . $id . '.*') as $archivoExistente) {
                if (is_file($archivoExistente)) {
                    unlink($archivoExistente);
                }
            }

            return $this->json($response, [
                'message' => 'Vehículo borrado correctamente'
            ], 200);
        } catch (Exception $exception) {
            return $this->jsonError($response, $exception);
        }
    }

    public function historial(Request $request, Response $response, array $args): Response
{
    try {

        $id = (int)$args['id'];

        $controller = new VehiculoController();

        $vehiculo = $controller->getHistorialReservas($id);

        return $this->json(
            $response,
            $vehiculo->toJson()
        );

    } catch (Exception $exception) {
        return $this->jsonError($response, $exception);
    }
}
}