<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Controllers;

use ALQUILERVEHICULOS\Models\Vehiculo;
use Exception;

class VehiculoController extends AbstractController
{
    public function getVehiculos()
    {
        $rows = Vehiculo::all();
        return $rows->toJson();
    }

    public function getVehiculosDisponibles()
    {
        $rows = Vehiculo::where('estado', 'disponible')->get();
        return $rows->toJson();
    }

    public function getVehiculo(int $id): Vehiculo
    {
        $vehiculo = Vehiculo::find($id);

        if (empty($vehiculo)) {
            throw new Exception("El vehículo $id no existe", 1);
        }

        return $vehiculo;
    }

    public function guardarVehiculo(array $data)
    {
        $this->validarDatos($data);

        $vehiculo = new Vehiculo();
        $vehiculo->marca = trim($data['marca']);
        $vehiculo->modelo = trim($data['modelo']);
        $vehiculo->anio = (int) $data['anio'];
        $vehiculo->categoria = $this->limpiarTexto($data['categoria'] ?? null);
        $vehiculo->estado = $data['estado'] ?? 'disponible';
        $vehiculo->save();

        return $vehiculo->toJson();
    }

    public function modificarVehiculo(int $id, array $data): Vehiculo
    {
        $this->validarDatos($data, true);

        $vehiculo = $this->getVehiculo($id);
        $vehiculo->marca = trim($data['marca']);
        $vehiculo->modelo = trim($data['modelo']);
        $vehiculo->anio = (int) $data['anio'];
        $vehiculo->categoria = $this->limpiarTexto($data['categoria'] ?? null);
        $vehiculo->estado = $data['estado'] ?? $vehiculo->estado;
        $vehiculo->save();

        return $vehiculo;
    }

    public function cambiarEstadoVehiculo(int $id, array $data): Vehiculo
    {
        $vehiculo = $this->getVehiculo($id);

        $estado = $data['estado'] ?? null;
        $this->validarEnListado(
            $estado,
            ['disponible', 'alquilado', 'mantenimiento'],
            'El estado del vehículo no es válido'
        );

        $vehiculo->estado = $estado;
        $vehiculo->save();

        return $vehiculo;
    }

    public function borrarVehiculo(int $id): void
    {
        $vehiculo = $this->getVehiculo($id);
        $vehiculo->delete();
    }

    protected function validarDatos(array $data, bool $isUpdate = false): void
    {
        $this->validarRequerido($data, 'marca', 'La marca es obligatoria');
        $this->validarRequerido($data, 'modelo', 'El modelo es obligatorio');
        $this->validarRequerido($data, 'anio', 'El año es obligatorio');

        $this->validarEnteroPositivo($data['anio'], 'El año del vehículo debe ser un número válido');
        $anio = (int)$data['anio'];

if ($anio < 1900 || $anio > (date('Y') + 1)) {
    throw new Exception(
        'El año del vehículo no es válido',
        2
    );
}

        $estado = $data['estado'] ?? 'disponible';
        $this->validarEnListado(
            $estado,
            ['disponible', 'alquilado', 'mantenimiento'],
            'El estado del vehículo no es válido'
        );
    }

    public function getHistorialReservas(int $id): Vehiculo
{
    $vehiculo = Vehiculo::with([
        'reservas.cliente'
    ])->find($id);

    if (empty($vehiculo)) {
        throw new Exception("El vehículo $id no existe", 1);
    }

    return $vehiculo;
}
}