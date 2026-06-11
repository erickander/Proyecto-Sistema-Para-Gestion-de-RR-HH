<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    protected $table = 'tbl_postulaciones';

    protected $primaryKey = 'id_postulacion';

    public $timestamps = false;

    protected $fillable = [
        'id_candidato',
        'id_vacante',
        'fecha_postulacion',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_postulacion' => 'datetime',
        ];
    }

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'id_candidato', 'id_candidato');
    }

    public function vacante()
    {
        return $this->belongsTo(Vacante::class, 'id_vacante', 'id_vacante');
    }

    public function analisisIa()
    {
        return $this->hasOne(AnalisisIa::class, 'id_postulacion', 'id_postulacion');
    }
}
