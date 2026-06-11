<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudVacacion extends Model
{
    protected $table = 'tbl_solicitudes_vacaciones';

    protected $primaryKey = 'id_solicitud';

    public $timestamps = false;

    protected $fillable = [
        'id_empleado',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'tipo_solicitud',
        'estado',
        'revisado_por',
        'fecha_solicitud',
        'fecha_revision',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_solicitud' => 'datetime',
            'fecha_revision' => 'datetime',
        ];
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
