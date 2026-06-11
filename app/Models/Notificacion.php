<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'tbl_notificaciones';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
        'fecha_envio',
        'fecha_lectura',
    ];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'fecha_envio' => 'datetime',
            'fecha_lectura' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
