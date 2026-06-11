<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidato extends Model
{
    protected $table = 'tbl_candidatos';

    protected $primaryKey = 'id_candidato';

    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'cedula',
        'correo',
        'telefono',
        'direccion',
        'cv_url',
        'fecha_registro',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
        ];
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_candidato', 'id_candidato');
    }
}
