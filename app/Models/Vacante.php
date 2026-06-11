<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacante extends Model
{
    protected $table = 'tbl_vacantes';

    protected $primaryKey = 'id_vacante';

    public $timestamps = false;

    protected $fillable = [
        'id_departamento',
        'titulo',
        'descripcion',
        'requisitos',
        'tipo_contrato',
        'salario_ofrecido',
        'estado',
        'fecha_publicacion',
        'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'salario_ofrecido' => 'decimal:2',
            'fecha_publicacion' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_vacante', 'id_vacante');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }
}
