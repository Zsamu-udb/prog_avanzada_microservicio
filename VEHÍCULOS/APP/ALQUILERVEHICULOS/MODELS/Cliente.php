<?php
declare(strict_types=1);

namespace ALQUILERVEHICULOS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ALQUILERVEHICULOS\Models\Reserva;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
        'numero_licencia'
    ];

    protected $attributes = [
        'telefono' => null,
        'correo' => null,
        'numero_licencia' => null
    ];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'cliente_id', 'id');
    }

    public function tieneCorreo(): bool
    {
        return !empty($this->correo);
    }

    public function tieneLicencia(): bool
    {
        return !empty($this->numero_licencia);
    }

    public function nombreFormateado(): string
    {
        return trim((string) $this->nombre);
    }
}