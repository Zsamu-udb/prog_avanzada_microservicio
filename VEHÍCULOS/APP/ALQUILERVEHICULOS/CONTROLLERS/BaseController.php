<?php
declare(strict_types=1);

namespace App\AlquilerVehiculos\Controllers;

abstract class BaseController
{
    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return is_array($data) ? $data : [];
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function successResponse(array $data, int $statusCode = 200): void
    {
        $this->jsonResponse([
            'success' => true,
            'data' => $data
        ], $statusCode);
    }

    protected function errorResponse(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
}