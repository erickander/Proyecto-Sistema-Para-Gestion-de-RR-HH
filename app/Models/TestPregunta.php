<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestPregunta extends Model
{
    protected $table = 'tbl_test_preguntas';

    protected $primaryKey = 'id_pregunta';

    protected $fillable = [
        'id_test',
        'pregunta',
        'tipo',
        'opciones',
        'respuesta_correcta',
        'puntaje_maximo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'opciones' => 'array',
            'puntaje_maximo' => 'decimal:2',
        ];
    }

    public function test()
    {
        return $this->belongsTo(TestVacante::class, 'id_test', 'id_test');
    }

    public function respuestas()
    {
        return $this->hasMany(TestRespuesta::class, 'id_pregunta', 'id_pregunta');
    }
}
