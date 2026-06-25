<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalisisIa extends Model
{
    protected $table = 'tbl_analisis_ia';

    protected $primaryKey = 'id_analisis';

    public $timestamps = false;

    protected $fillable = [
        'id_postulacion',
        'modelo_utilizado',
        'version_modelo',
        'puntuacion_general',
        'puntaje_cv',
        'puntaje_test',
        'compatibilidad',
        'experiencia_score',
        'habilidades_score',
        'educacion_score',
        'idiomas_score',
        'observaciones',
        'habilidades_detectadas',
        'estudios_detectados',
        'experiencia_detectada',
        'fortalezas',
        'debilidades',
        'resultado_json',
        'analisis_test',
        'recomendacion',
        'fecha_analisis',
    ];

    protected function casts(): array
    {
        return [
            'puntuacion_general' => 'decimal:2',
            'puntaje_cv' => 'decimal:2',
            'puntaje_test' => 'decimal:2',
            'compatibilidad' => 'decimal:2',
            'experiencia_score' => 'decimal:2',
            'habilidades_score' => 'decimal:2',
            'educacion_score' => 'decimal:2',
            'idiomas_score' => 'decimal:2',
            'fecha_analisis' => 'datetime',
        ];
    }

    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class, 'id_postulacion', 'id_postulacion');
    }
}
