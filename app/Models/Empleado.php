<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'tbl_empleados';

    protected $primaryKey = 'id_empleado';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_departamento',
        'cedula',
        'nombres',
        'apellidos',
        'telefono',
        'direccion',
        'correo',
        'cargo',
        'salario_base',
        'fecha_ingreso',
        'fecha_salida',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_salida' => 'date',
            'salario_base' => 'decimal:2',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function nominas()
    {
        return $this->hasMany(Nomina::class, 'id_empleado', 'id_empleado');
    }

    public function solicitudesVacaciones()
    {
        return $this->hasMany(SolicitudVacacion::class, 'id_empleado', 'id_empleado');
    }
}
