<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'tbl_departamentos';

    protected $primaryKey = 'id_departamento';

    public $timestamps = false;

    protected $fillable = [
        'nombre_departamento',
        'descripcion',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_departamento', 'id_departamento');
    }
}
