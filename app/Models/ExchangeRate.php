<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable; // Para mantener tu estándar

class ExchangeRate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'currency_from', // ej: 'USD'
        'currency_to',   // ej: 'VES' (Bolívares)
        'rate',          // ej: 38.54
        'date',          // Fecha a la que aplica
        'is_active',      // Para saber cuál es la tasa de "hoy" o del momento
    ];

    protected $casts = [
        'rate' => 'decimal:4', // 4 decimales suele ser seguro para divisas
        'date' => 'date',
        'is_active' => 'boolean',
    ];
}
