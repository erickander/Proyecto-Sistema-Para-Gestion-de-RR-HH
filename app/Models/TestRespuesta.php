<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestRespuesta extends Model
{
    protected $table = 'tbl_test_respuestas';

    protected $primaryKey = 'id_respuesta';

    protected $fillable = [
        'id_postulacion',
        'id_pregunta',
        'respuesta',
        'puntaje_ia',
        'observacion_ia',
    ];

    protected function casts(): array
    {
        return [
            'puntaje_ia' => 'decimal:2',
        ];
    }

    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class, 'id_postulacion', 'id_postulacion');
    }

    public function pregunta()
    {
        return $this->belongsTo(TestPregunta::class, 'id_pregunta', 'id_pregunta');
    }
}
