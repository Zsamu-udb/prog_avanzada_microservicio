<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ALQUILERVEHICULOS\Models\Reserva;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'marca',
        'modelo',
        'anio',
        'categoria',
        'estado'
    ];

    protected $attributes = [
        'categoria' => null,
        'estado' => 'disponible'
    ];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'vehiculo_id', 'id');
    }

    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }

    public function estaAlquilado(): bool
    {
        return $this->estado === 'alquilado';
    }

    public function estaEnMantenimiento(): bool
    {
        return $this->estado === 'mantenimiento';
    }

    public function nombreCompleto(): string
    {
        return trim((string) $this->marca . ' ' . $this->modelo);
    }
}