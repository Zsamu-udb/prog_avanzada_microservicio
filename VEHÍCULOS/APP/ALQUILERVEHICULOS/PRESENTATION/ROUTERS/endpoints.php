<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use ALQUILERVEHICULOS\Presentation\Repositories\ClienteRepository;
use ALQUILERVEHICULOS\Presentation\Repositories\VehiculoRepository;
use ALQUILERVEHICULOS\Presentation\Repositories\ReservaRepository;
use ALQUILERVEHICULOS\Presentation\Repositories\TestRepository;

return function (App $app): void {
    $clienteRepository = new ClienteRepository();
    $vehiculoRepository = new VehiculoRepository();
    $reservaRepository = new ReservaRepository();
    $testRepository = new TestRepository();

    $app->get('/', [$testRepository, 'default']);

    $app->group('/clientes', function (RouteCollectorProxy $group) use ($clienteRepository) {
        $group->get('', [$clienteRepository, 'all']);
        $group->post('', [$clienteRepository, 'create']);
        $group->get('/{id}', [$clienteRepository, 'detail']);
        $group->put('/{id}', [$clienteRepository, 'update']);
        $group->delete('/{id}', [$clienteRepository, 'delete']);
    });

    $app->group('/vehiculos', function (RouteCollectorProxy $group) use ($vehiculoRepository) {
        $group->get('', [$vehiculoRepository, 'all']);
        $group->post('', [$vehiculoRepository, 'create']);
        $group->get('/disponibles', [$vehiculoRepository, 'disponibles']);
        $group->get('/{id}', [$vehiculoRepository, 'detail']);
        $group->put('/{id}', [$vehiculoRepository, 'update']);
        $group->patch('/{id}/estado', [$vehiculoRepository, 'changeEstado']);
        $group->delete('/{id}', [$vehiculoRepository, 'delete']);
    });

    $app->group('/reservas', function (RouteCollectorProxy $group) use ($reservaRepository) {
        $group->get('', [$reservaRepository, 'all']);
        $group->post('', [$reservaRepository, 'create']);
        $group->get('/{id}', [$reservaRepository, 'detail']);
        $group->put('/{id}', [$reservaRepository, 'update']);
        $group->patch('/{id}/completar', [$reservaRepository, 'completar']);
        $group->patch('/{id}/cancelar', [$reservaRepository, 'cancelar']);
        $group->delete('/{id}', [$reservaRepository, 'delete']);
    });
};