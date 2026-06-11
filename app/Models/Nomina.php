<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nomina extends Model
{
    protected $table = 'tbl_nominas';

    protected $primaryKey = 'id_nomina';

    public $timestamps = false;

    protected $fillable = [
        'id_empleado',
        'periodo_inicio',
        'periodo_fin',
        'salario_base',
        'horas_extra',
        'monto_horas_extra',
        'descuentos',
        'total_pagar',
        'estado',
        'fecha_generacion',
    ];

    protected function casts(): array
    {
        return [
            'periodo_inicio' => 'date',
            'periodo_fin' => 'date',
            'salario_base' => 'decimal:2',
            'horas_extra' => 'decimal:2',
            'monto_horas_extra' => 'decimal:2',
            'descuentos' => 'decimal:2',
            'total_pagar' => 'decimal:2',
            'fecha_generacion' => 'datetime',
        ];
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
