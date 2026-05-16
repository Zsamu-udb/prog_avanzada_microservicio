<?php
declare(strict_types=1);

use App\AlquilerVehiculos\Controllers\ClienteController;
use App\AlquilerVehiculos\Controllers\VehiculoController;
use App\AlquilerVehiculos\Controllers\ReservaController;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app): void {
    $clienteController = new ClienteController();
    $vehiculoController = new VehiculoController();
    $reservaController = new ReservaController();

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */
    $app->get('/clientes', function (Request $request, Response $response) use ($clienteController) {
        $clienteController->index();
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/clientes', function (Request $request, Response $response) use ($clienteController) {
        $clienteController->store();
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/clientes/{id}', function (Request $request, Response $response, array $args) use ($clienteController) {
        $clienteController->show((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/clientes/{id}', function (Request $request, Response $response, array $args) use ($clienteController) {
        $clienteController->update((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/clientes/{id}', function (Request $request, Response $response, array $args) use ($clienteController) {
        $clienteController->destroy((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    /*
    |--------------------------------------------------------------------------
    | VEHICULOS
    |--------------------------------------------------------------------------
    */
    $app->get('/vehiculos', function (Request $request, Response $response) use ($vehiculoController) {
        $vehiculoController->index();
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/vehiculos/disponibles', function (Request $request, Response $response) use ($vehiculoController) {
        $vehiculoController->disponibles();
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/vehiculos', function (Request $request, Response $response) use ($vehiculoController) {
        $vehiculoController->store();
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/vehiculos/{id}', function (Request $request, Response $response, array $args) use ($vehiculoController) {
        $vehiculoController->show((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/vehiculos/{id}', function (Request $request, Response $response, array $args) use ($vehiculoController) {
        $vehiculoController->update((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/vehiculos/{id}/estado', function (Request $request, Response $response, array $args) use ($vehiculoController) {
        $vehiculoController->updateEstado((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/vehiculos/{id}', function (Request $request, Response $response, array $args) use ($vehiculoController) {
        $vehiculoController->destroy((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    /*
    |--------------------------------------------------------------------------
    | RESERVAS
    |--------------------------------------------------------------------------
    */
    $app->get('/reservas', function (Request $request, Response $response) use ($reservaController) {
        $reservaController->index();
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/reservas', function (Request $request, Response $response) use ($reservaController) {
        $reservaController->store();
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/reservas/{id}', function (Request $request, Response $response, array $args) use ($reservaController) {
        $reservaController->show((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/reservas/{id}', function (Request $request, Response $response, array $args) use ($reservaController) {
        $reservaController->update((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/reservas/{id}/completar', function (Request $request, Response $response, array $args) use ($reservaController) {
        $reservaController->completar((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/reservas/{id}/cancelar', function (Request $request, Response $response, array $args) use ($reservaController) {
        $reservaController->cancelar((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/reservas/{id}', function (Request $request, Response $response, array $args) use ($reservaController) {
        $reservaController->destroy((int) $args['id']);
        return $response->withHeader('Content-Type', 'application/json');
    });

    /*
    |--------------------------------------------------------------------------
    | RUTAS DE PRUEBA
    |--------------------------------------------------------------------------
    */
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'message' => 'API de alquiler de vehiculos funcionando correctamente'
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/test', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'message' => 'Ruta de prueba OK'
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    });
};