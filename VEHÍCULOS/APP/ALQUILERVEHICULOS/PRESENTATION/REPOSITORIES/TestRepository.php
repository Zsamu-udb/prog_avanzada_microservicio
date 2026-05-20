<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Presentation\Repositories;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestRepository
{
    public function default(Request $request, Response $response): Response
    {
        $response->getBody()->write('API de alquiler de vehículos funcionando');
        return $response;
    }
}