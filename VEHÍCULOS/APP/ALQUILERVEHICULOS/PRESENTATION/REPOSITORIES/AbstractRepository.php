<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Presentation\Repositories;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractRepository
{
    protected function json(Response $response, $data, int $status = 200): Response
    {
        $payload = is_string($data)
            ? $data
            : json_encode($data, JSON_UNESCAPED_UNICODE);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    protected function jsonError(Response $response, Exception $exception): Response
    {
        $status = 400;

        if ($exception->getCode() === 1) {
            $status = 404;
        } elseif ($exception->getCode() === 3) {
            $status = 409;
        }

        return $this->json($response, [
            'error' => true,
            'message' => $exception->getMessage()
        ], $status);
    }

    protected function obtenerDatos(Request $request): array
    {
        $data = $request->getParsedBody();

        if (is_array($data)) {
            return $data;
        }

        $rawBody = (string) $request->getBody();
        $decoded = json_decode($rawBody, true);

        return is_array($decoded) ? $decoded : [];
    }
}