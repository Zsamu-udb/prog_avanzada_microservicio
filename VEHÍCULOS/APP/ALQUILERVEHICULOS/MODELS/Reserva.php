<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ALQUILERVEHICULOS\Models\Cliente;
use ALQUILERVEHICULOS\Models\Vehiculo;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'cliente_id',
        'vehiculo_id',
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    protected $attributes = [
        'estado' => 'activa'
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id');
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'id');
    }

    public function estaActiva(): bool
    {
        return $this->estado === 'activa';
    }

    public function estaCompletada(): bool
    {
        return $this->estado === 'completada';
    }

    public function estaCancelada(): bool
    {
        return $this->estado === 'cancelada';
    }

    public function rangoFechas(): string
    {
        return $this->fecha_inicio . ' / ' . $this->fecha_fin;
    }
}