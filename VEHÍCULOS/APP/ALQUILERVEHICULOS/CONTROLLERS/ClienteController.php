<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Controllers;

use ALQUILERVEHICULOS\Models\Cliente;
use Exception;

class ClienteController extends AbstractController
{
    public function getClientes()
    {
        $rows = Cliente::all();
        return $rows->toJson();
    }

    public function guardarCliente(array $data)
    {
        $this->validarDatos($data);

        $cliente = new Cliente();
        $cliente->nombre = trim($data['nombre']);
        $cliente->telefono = $this->limpiarTexto($data['telefono'] ?? null);
        $cliente->correo = $this->limpiarTexto($data['correo'] ?? null);
        $cliente->numero_licencia = $this->limpiarTexto($data['numero_licencia'] ?? null);
        $cliente->save();

        return $cliente->toJson();
    }

    public function getCliente(int $id): Cliente
    {
        $cliente = Cliente::find($id);

        if (empty($cliente)) {
            throw new Exception("El cliente $id no existe", 1);
        }

        return $cliente;
    }

    public function modificarCliente(int $id, array $data): Cliente
    {
        $this->validarDatos($data, true);

        $cliente = $this->getCliente($id);
        $cliente->nombre = trim($data['nombre']);
        $cliente->telefono = $this->limpiarTexto($data['telefono'] ?? null);
        $cliente->correo = $this->limpiarTexto($data['correo'] ?? null);
        $cliente->numero_licencia = $this->limpiarTexto($data['numero_licencia'] ?? null);
        $cliente->save();

        return $cliente;
    }

    public function borrarCliente(int $id): void
    {
        $cliente = $this->getCliente($id);
        $cliente->delete();
    }

    protected function validarDatos(array $data, bool $isUpdate = false): void
    {
        $this->validarRequerido($data, 'nombre', 'El nombre del cliente es obligatorio');

        $correo = $this->limpiarTexto($data['correo'] ?? null);
        $this->validarEmailOpcional($correo);
    }
}