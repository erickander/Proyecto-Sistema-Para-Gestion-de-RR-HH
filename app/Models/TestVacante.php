<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestVacante extends Model
{
    protected $table = 'tbl_tests_vacantes';

    protected $primaryKey = 'id_test';

    protected $fillable = [
        'id_vacante',
        'titulo',
        'descripcion',
        'estado',
    ];

    public function vacante()
    {
        return $this->belongsTo(Vacante::class, 'id_vacante', 'id_vacante');
    }

    public function preguntas()
    {
        return $this->hasMany(TestPregunta::class, 'id_test', 'id_test')->orderBy('orden');
    }
}
