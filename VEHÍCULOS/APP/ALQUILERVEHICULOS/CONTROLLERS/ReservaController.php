<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Controllers;

use ALQUILERVEHICULOS\Models\Reserva;
use ALQUILERVEHICULOS\Models\Cliente;
use ALQUILERVEHICULOS\Models\Vehiculo;
use Exception;

class ReservaController extends AbstractController
{
    public function getReservas()
    {
        $rows = Reserva::with(['cliente', 'vehiculo'])->get();
        return $rows->toJson();
    }

    public function getReserva(int $id): Reserva
    {
        $reserva = Reserva::with(['cliente', 'vehiculo'])->find($id);

        if (empty($reserva)) {
            throw new Exception("La reserva $id no existe", 1);
        }

        return $reserva;
    }

    public function guardarReserva(array $data)
    {
        $this->validarDatos($data);

        $cliente = Cliente::find((int) $data['cliente_id']);
        if (empty($cliente)) {
            throw new Exception("El cliente {$data['cliente_id']} no existe", 1);
        }

        $vehiculo = Vehiculo::find((int) $data['vehiculo_id']);
        if (empty($vehiculo)) {
            throw new Exception("El vehículo {$data['vehiculo_id']} no existe", 1);
        }

        if (!$vehiculo->estaDisponible()) {
            throw new Exception("El vehículo {$data['vehiculo_id']} no está disponible", 3);
        }

        $conflicto = Reserva::where(
    'vehiculo_id',
    $data['vehiculo_id']
)
->where('estado', 'activa')
->where(function ($query) use ($data) {

    $query
        ->whereBetween(
            'fecha_inicio',
            [
                $data['fecha_inicio'],
                $data['fecha_fin']
            ]
        )
        ->orWhereBetween(
            'fecha_fin',
            [
                $data['fecha_inicio'],
                $data['fecha_fin']
            ]
        )
        ->orWhere(function ($q) use ($data) {

            $q->where(
                'fecha_inicio',
                '<=',
                $data['fecha_inicio']
            )
            ->where(
                'fecha_fin',
                '>=',
                $data['fecha_fin']
            );

        });

})
->exists();

if ($conflicto) {
    throw new Exception(
        'Ya existe una reserva activa para ese período',
        3
    );
}

        $reserva = new Reserva();
        $reserva->cliente_id = (int) $data['cliente_id'];
        $reserva->vehiculo_id = (int) $data['vehiculo_id'];
        $reserva->fecha_inicio = $data['fecha_inicio'];
        $reserva->fecha_fin = $data['fecha_fin'];
        $reserva->estado = $data['estado'] ?? 'activa';
        $reserva->save();

        $vehiculo->estado = 'alquilado';
        $vehiculo->save();

        return $reserva->fresh(['cliente', 'vehiculo'])->toJson();
    }

    public function modificarReserva(int $id, array $data): Reserva
    {
        $this->validarDatos($data, true);

        $reserva = $this->getReserva($id);

        $cliente = Cliente::find((int) $data['cliente_id']);
        if (empty($cliente)) {
            throw new Exception("El cliente {$data['cliente_id']} no existe", 1);
        }

        $vehiculo = Vehiculo::find((int) $data['vehiculo_id']);
        if (empty($vehiculo)) {
            throw new Exception("El vehículo {$data['vehiculo_id']} no existe", 1);
        }

        if ((int) $reserva->vehiculo_id !== (int) $data['vehiculo_id'] && !$vehiculo->estaDisponible()) {
            throw new Exception("El vehículo {$data['vehiculo_id']} no está disponible", 3);
        }

        $vehiculoAnterior = Vehiculo::find((int) $reserva->vehiculo_id);

        $reserva->cliente_id = (int) $data['cliente_id'];
        $reserva->vehiculo_id = (int) $data['vehiculo_id'];
        $reserva->fecha_inicio = $data['fecha_inicio'];
        $reserva->fecha_fin = $data['fecha_fin'];
        $reserva->estado = $data['estado'] ?? $reserva->estado;
        $reserva->save();

        if (!empty($vehiculoAnterior) && (int) $vehiculoAnterior->id !== (int) $vehiculo->id) {
            $vehiculoAnterior->estado = 'disponible';
            $vehiculoAnterior->save();
        }

        if ($reserva->estado === 'activa') {
            $vehiculo->estado = 'alquilado';
        } else {
            $vehiculo->estado = 'disponible';
        }
        $vehiculo->save();

        return $reserva->fresh(['cliente', 'vehiculo']);
    }

    public function completarReserva(int $id): Reserva
    {
        $reserva = $this->getReserva($id);

        if ($reserva->estaCompletada()) {
            throw new Exception("La reserva $id ya está completada", 2);
        }

        $reserva->estado = 'completada';
        $reserva->save();

        $vehiculo = Vehiculo::find((int) $reserva->vehiculo_id);
        if (!empty($vehiculo)) {
            $vehiculo->estado = 'disponible';
            $vehiculo->save();
        }

        return $reserva->fresh(['cliente', 'vehiculo']);
    }

    public function cancelarReserva(int $id): Reserva
    {
        $reserva = $this->getReserva($id);

        if ($reserva->estaCancelada()) {
            throw new Exception("La reserva $id ya está cancelada", 2);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        $vehiculo = Vehiculo::find((int) $reserva->vehiculo_id);
        if (!empty($vehiculo)) {
            $vehiculo->estado = 'disponible';
            $vehiculo->save();
        }

        return $reserva->fresh(['cliente', 'vehiculo']);
    }

    public function borrarReserva(int $id): void
    {
        $reserva = $this->getReserva($id);
        $vehiculoId = (int) $reserva->vehiculo_id;

        $reserva->delete();

        $vehiculo = Vehiculo::find($vehiculoId);
        if (!empty($vehiculo)) {
            $vehiculo->estado = 'disponible';
            $vehiculo->save();
        }
    }

    protected function validarDatos(array $data, bool $isUpdate = false): void
    {
        $this->validarRequerido($data, 'cliente_id', 'El cliente es obligatorio');
        $this->validarRequerido($data, 'vehiculo_id', 'El vehículo es obligatorio');
        $this->validarRequerido($data, 'fecha_inicio', 'La fecha de inicio es obligatoria');
        $this->validarRequerido($data, 'fecha_fin', 'La fecha final es obligatoria');

        $this->validarEnteroPositivo($data['cliente_id'], 'El id del cliente no es válido');
        $this->validarEnteroPositivo($data['vehiculo_id'], 'El id del vehículo no es válido');

        $this->validarFecha($data['fecha_inicio'], 'La fecha de inicio no es válida');
        $this->validarFecha($data['fecha_fin'], 'La fecha final no es válida');
        $this->validarRangoFechas($data['fecha_inicio'], $data['fecha_fin']);
        if ($data['fecha_inicio'] < date('Y-m-d')) {
    throw new Exception(
        'No se permiten reservas en fechas pasadas',
        2
    );
}

        $estado = $data['estado'] ?? 'activa';
        $this->validarEnListado(
            $estado,
            ['activa', 'completada', 'cancelada'],
            'El estado de la reserva no es válido'
        );
    }
}