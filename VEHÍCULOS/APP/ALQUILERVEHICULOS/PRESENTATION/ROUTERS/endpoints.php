<?php
declare(strict_types=1);

use App\AlquilerVehiculos\Controllers\ClienteController;
use App\AlquilerVehiculos\Controllers\VehiculoController;
use App\AlquilerVehiculos\Controllers\ReservaController;
use App\AlquilerVehiculos\Controllers\DevolucionController;

$controller = new ClienteController();
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = rtrim($uri, '/');

if ($uri === '/clientes' && $method === 'GET') {
    $controller->index();
    return;
}

if ($uri === '/clientes' && $method === 'POST') {
    $controller->store();
    return;
}

if (preg_match('#^/clientes/(\d+)$#', $uri, $matches)) {
    $id = (int) $matches[1];

    if ($method === 'GET') {
        $controller->show($id);
        return;
    }

    if ($method === 'PUT') {
        $controller->update($id);
        return;
    }

    if ($method === 'DELETE') {
        $controller->destroy($id);
        return;
    }
}

$vehiculoController = new VehiculoController();

if ($uri === '/vehiculos' && $method === 'GET') {
    $vehiculoController->index();
    return;
}

if ($uri === '/vehiculos/disponibles' && $method === 'GET') {
    $vehiculoController->disponibles();
    return;
}

if ($uri === '/vehiculos' && $method === 'POST') {
    $vehiculoController->store();
    return;
}

if (preg_match('#^/vehiculos/(\d+)$#', $uri, $matches)) {
    $id = (int) $matches[1];

    if ($method === 'GET') {
        $vehiculoController->show($id);
        return;
    }

    if ($method === 'PUT') {
        $vehiculoController->update($id);
        return;
    }

    if ($method === 'DELETE') {
        $vehiculoController->destroy($id);
        return;
    }
}

if (preg_match('#^/vehiculos/(\d+)/estado$#', $uri, $matches) && $method === 'PUT') {
    $id = (int) $matches[1];
    $vehiculoController->changeEstado($id);
    return;
}

$reservaController = new ReservaController();

if ($uri === '/reservas' && $method === 'GET') {
    $reservaController->index();
    return;
}

if ($uri === '/reservas' && $method === 'POST') {
    $reservaController->store();
    return;
}

if (preg_match('#^/reservas/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $id = (int) $matches[1];
    $reservaController->show($id);
    return;
}

if (preg_match('#^/reservas/(\d+)/finalizar$#', $uri, $matches) && $method === 'PUT') {
    $id = (int) $matches[1];
    $reservaController->finalizar($id);
    return;
}

if (preg_match('#^/reservas/(\d+)/cancelar$#', $uri, $matches) && $method === 'PUT') {
    $id = (int) $matches[1];
    $reservaController->cancelar($id);
    return;
}

$devolucionController = new DevolucionController();

if ($uri === '/devoluciones' && $method === 'GET') {
    $devolucionController->index();
    return;
}

if ($uri === '/devoluciones' && $method === 'POST') {
    $devolucionController->store();
    return;
}

if (preg_match('#^/devoluciones/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $id = (int) $matches[1];
    $devolucionController->show($id);
    return;
}

http_response_code(404);
echo json_encode(['message' => 'Ruta no encontrada'], JSON_UNESCAPED_UNICODE);