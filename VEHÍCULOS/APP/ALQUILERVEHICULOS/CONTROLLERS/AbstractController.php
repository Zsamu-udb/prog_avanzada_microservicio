<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Controllers;

use Exception;

abstract class AbstractController
{
    abstract protected function validarDatos(array $data, bool $isUpdate = false): void;

    protected function validarRequerido(array $data, string $campo, string $mensaje): void
    {
        if (!isset($data[$campo]) || trim((string) $data[$campo]) === '') {
            throw new Exception($mensaje, 2);
        }
    }

    protected function validarEnteroPositivo($valor, string $mensaje): void
    {
        if (!filter_var($valor, FILTER_VALIDATE_INT) || (int) $valor <= 0) {
            throw new Exception($mensaje, 2);
        }
    }

    protected function validarEmailOpcional(?string $email): void
    {
        if ($email !== null && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo no tiene un formato válido', 2);
        }
    }

    protected function validarEnListado(?string $valor, array $permitidos, string $mensaje): void
    {
        if ($valor !== null && !in_array($valor, $permitidos, true)) {
            throw new Exception($mensaje, 2);
        }
    }

    protected function validarFecha(string $fecha, string $mensaje): void
    {
        $date = date_create($fecha);
        if (!$date || date_format($date, 'Y-m-d') !== $fecha) {
            throw new Exception($mensaje, 2);
        }
    }

    protected function validarRangoFechas(string $fechaInicio, string $fechaFin): void
    {
        if ($fechaInicio > $fechaFin) {
            throw new Exception('La fecha de inicio no puede ser mayor que la fecha final', 2);
        }
    }

    protected function limpiarTexto(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);
        return $valor === '' ? null : $valor;
    }
}